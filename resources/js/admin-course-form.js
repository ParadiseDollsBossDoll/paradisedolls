window.adminCourseForm = function adminCourseForm(config) {
    return {
        platform: config.platform ?? '',
        platformColor: config.platformColor ?? '#C9A96E',
        courseId: config.courseId ?? '',
        showSuggestions: config.showSuggestions ?? false,
        hasCourseOutline: !!config.hasCourseOutline,
        hasIntro: !!config.hasIntro,
        introVideo: {},
        modules: [],
        lessons: [],
        suggestions: config.suggestions ?? [],
        colors: config.colors ?? [],
        bunnyModalOpen: false,
        bunnyTarget: null,
        bunnySearch: '',
        bunnyVideos: [],
        bunnyLoading: false,
        bunnyError: null,
        uploads: {},
        lessonMoveNotice: null,

        // ── Autosave state ──────────────────────────────────────────────────
        autosave: {
            status: 'idle',   // 'idle' | 'saving' | 'saved' | 'error' | 'offline'
            message: '',
            lastSaved: null,
            timers: {},       // debounce timers keyed by lesson client_key
            unsaved: {},      // lessons waiting to flush when online: { client_key: lesson }
            isOnline: true,
        },
        draftAvailable: null, // { lessonCount, moduleCount, timestamp, draft }

        // ── Lifecycle ───────────────────────────────────────────────────────

        init() {
            this.introVideo = this.normalizedIntroVideo(config.introVideo ?? {});
            this.modules = this.normalizedModules(config.modules ?? [], config.lessons ?? []);
            this.lessons = (config.lessons ?? []).map((lesson, index) => this.normalizedLesson(lesson, index));

            // Online / offline detection
            this.autosave.isOnline = navigator.onLine;
            window.addEventListener('online', () => {
                this.autosave.isOnline = true;
                this.setAutosaveStatus('idle', '');
                this.flushPendingAutosaves();
            });
            window.addEventListener('offline', () => {
                this.autosave.isOnline = false;
                this.setAutosaveStatus('offline', 'Offline — changes saved locally');
            });

            // Restore localStorage draft if available and newer than server data
            this.loadLocalDraft();

            // Periodic local backup every 15 seconds
            setInterval(() => this.saveLocalDraft(), 15000);
        },

        // ── Autosave status helpers ─────────────────────────────────────────

        setAutosaveStatus(status, message = '') {
            this.autosave.status = status;
            this.autosave.message = message;
            if (status === 'saved') {
                this.autosave.lastSaved = new Date().toLocaleTimeString();
            }
        },

        get autosaveStatusLabel() {
            if (this.autosave.status === 'saving') return 'Saving…';
            if (this.autosave.status === 'saved') return `Saved at ${this.autosave.lastSaved}`;
            if (this.autosave.status === 'error') return this.autosave.message || 'Save failed';
            if (this.autosave.status === 'offline') return 'Offline — changes saved locally';
            if (Object.keys(this.autosave.unsaved).length > 0) return 'Unsaved changes';
            return '';
        },

        // ── localStorage draft ──────────────────────────────────────────────

        draftKey() {
            return `pd_course_draft_${config.courseId || 'new'}`;
        },

        saveLocalDraft() {
            if (!config.courseId) return;
            try {
                const draft = {
                    timestamp: Date.now(),
                    courseId: config.courseId,
                    modules: this.modules,
                    lessons: this.lessons,
                };
                localStorage.setItem(this.draftKey(), JSON.stringify(draft));
            } catch (_) {
                // Ignore quota errors (private mode, storage full, etc.)
            }
        },

        clearLocalDraft() {
            try {
                localStorage.removeItem(this.draftKey());
            } catch (_) {}
        },

        loadLocalDraft() {
            if (!config.courseId) return;
            try {
                const stored = localStorage.getItem(this.draftKey());
                if (!stored) return;
                const draft = JSON.parse(stored);

                // Ignore drafts older than 24 hours
                if (Date.now() - draft.timestamp > 86400000) {
                    this.clearLocalDraft();
                    return;
                }

                // Only prompt if the draft has MORE lessons than what the server returned,
                // which indicates unsaved work from a previous session.
                const draftLessons = draft.lessons?.length ?? 0;
                const serverLessons = this.lessons.length;

                if (draftLessons > serverLessons) {
                    this.draftAvailable = {
                        lessonCount: draftLessons,
                        moduleCount: draft.modules?.length ?? 0,
                        timestamp: draft.timestamp,
                        draft,
                    };
                }
            } catch (_) {}
        },

        restoreDraft() {
            if (!this.draftAvailable) return;
            const { draft } = this.draftAvailable;
            this.modules = this.normalizedModules(draft.modules ?? [], draft.lessons ?? []);
            this.lessons = (draft.lessons ?? []).map((lesson, index) => this.normalizedLesson(lesson, index));
            this.draftAvailable = null;
            this.setAutosaveStatus('idle', 'Draft restored — review your lessons and save.');
        },

        discardDraft() {
            this.clearLocalDraft();
            this.draftAvailable = null;
        },

        // ── Per-lesson autosave ─────────────────────────────────────────────

        scheduleAutosaveLesson(lesson, event) {
            // Ignore file input changes — those are handled by dedicated upload handlers
            if (event?.target?.type === 'file') return;
            if (!config.autosaveUrls?.lessonSave) {
                this.saveLocalDraft();
                return;
            }

            const key = lesson.client_key;
            this.autosave.unsaved[key] = lesson;
            this.setAutosaveStatus('saving', 'Saving…');
            this.saveLocalDraft();

            clearTimeout(this.autosave.timers[key]);
            this.autosave.timers[key] = setTimeout(() => {
                this.autosaveLessonNow(lesson);
            }, 1800);
        },

        async autosaveLessonNow(lesson) {
            if (!config.autosaveUrls?.lessonSave) return;

            if (!this.autosave.isOnline) {
                this.saveLocalDraft();
                return;
            }

            const isNew = !lesson.id;
            const clientKey = lesson.client_key;

            try {
                this.setAutosaveStatus('saving', 'Saving…');

                const url = isNew
                    ? config.autosaveUrls.lessonSave
                    : config.autosaveUrls.lessonSave.replace('/autosave', `/${lesson.id}/autosave`);

                const response = await fetch(url, {
                    method: isNew ? 'POST' : 'PUT',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify(this.lessonAutosavePayload(lesson)),
                });

                const data = await this.jsonResponse(response);

                // Stamp the lesson with its server ID so future saves use PUT
                if (isNew && data.id) {
                    const found = this.lessons.find((l) => l.client_key === clientKey);
                    if (found) {
                        const oldKey = this.lessonKey(found);
                        found.id = data.id;
                        found.course_id = config.courseId;
                        // Notify nested components that this lesson's tab key changed
                        window.dispatchEvent(new CustomEvent('pd:lesson-id-updated', {
                            detail: { oldKey, newKey: this.lessonKey(found) },
                        }));
                    }
                }

                delete this.autosave.unsaved[clientKey];
                this.clearLocalDraft();
                this.setAutosaveStatus('saved');

            } catch (error) {
                this.setAutosaveStatus('error', `Save failed: ${error.message}`);
                this.saveLocalDraft();
            }
        },

        lessonAutosavePayload(lesson) {
            return {
                title: lesson.title,
                course_module_id: lesson.course_module_id || null,
                module_title: lesson.module_title || null,
                body: lesson.body || '',
                overview: lesson.overview || '',
                steps: lesson.steps || '',
                tips: lesson.tips || '',
                safety_notes: lesson.safety_notes || '',
                resource_links: lesson.resource_links || '',
                is_published: lesson.is_published,
                bunny_video_id: lesson.bunny_video_id || null,
                bunny_library_id: lesson.bunny_library_id || null,
                bunny_video_title: lesson.bunny_video_title || null,
                bunny_thumbnail_url: lesson.bunny_thumbnail_url || null,
                bunny_upload_fingerprint: lesson.bunny_upload_fingerprint || null,
                bunny_status: lesson.bunny_status || null,
                duration: lesson.duration || null,
                pdf_url: lesson.pdf_url || null,
                presentation_url: lesson.presentation_url || null,
                sort_order: lesson.sort_order,
                content_blocks_enabled: true,
                _content_block_count: lesson.content_blocks?.length ?? 0,
                content_blocks: (lesson.content_blocks ?? []).map((block) => ({
                    id: block.id || null,
                    block_type: block.block_type,
                    title: block.title || null,
                    content: block.content || null,
                    image_path: block.image_path || null,
                    file_path: block.file_path || null,
                    slide_images: Array.isArray(block.slide_images) ? block.slide_images : [],
                    presentation_url: block.presentation_url || null,
                    bunny_video_id: block.bunny_video_id || null,
                    bunny_library_id: block.bunny_library_id || null,
                    bunny_video_title: block.bunny_video_title || null,
                    bunny_thumbnail_url: block.bunny_thumbnail_url || null,
                    bunny_upload_fingerprint: block.bunny_upload_fingerprint || null,
                    bunny_status: block.bunny_status || null,
                    duration: block.duration || null,
                    sort_order: block.sort_order,
                })),
            };
        },

        // ── Module autosave ─────────────────────────────────────────────────

        async autosaveModule(module) {
            if (!config.autosaveUrls?.moduleSave) {
                this.saveLocalDraft();
                return;
            }
            if (!this.autosave.isOnline) {
                this.saveLocalDraft();
                return;
            }

            const isNew = !module.id;
            const clientKey = module.client_key;

            try {
                this.setAutosaveStatus('saving', 'Saving module…');

                const url = isNew
                    ? config.autosaveUrls.moduleSave
                    : `${config.autosaveUrls.moduleSave}/${module.id}`;

                const response = await fetch(url, {
                    method: isNew ? 'POST' : 'PUT',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({
                        client_key: clientKey,
                        title: module.title || 'Core Training',
                        description: module.description || null,
                        is_published: module.is_published,
                        sort_order: module.sort_order,
                    }),
                });

                const data = await this.jsonResponse(response);

                if (isNew && data.id) {
                    const found = this.modules.find((m) => m.client_key === clientKey);
                    if (found) {
                        found.id = data.id;
                        // Update any lessons that used client_key to reference this module
                        this.lessons.forEach((lesson) => {
                            if (lesson.module_key === clientKey) {
                                lesson.course_module_id = data.id;
                            }
                        });
                    }
                }

                this.setAutosaveStatus('saved');

            } catch (error) {
                this.setAutosaveStatus('error', `Module save failed: ${error.message}`);
                this.saveLocalDraft();
            }
        },

        scheduleAutosaveModule(module, event) {
            if (event?.target?.type === 'file') return;
            if (!config.autosaveUrls?.moduleSave) {
                this.saveLocalDraft();
                return;
            }
            const key = module.client_key;
            this.setAutosaveStatus('saving', 'Saving…');
            this.saveLocalDraft();

            clearTimeout(this.autosave.timers[`module-${key}`]);
            this.autosave.timers[`module-${key}`] = setTimeout(() => {
                this.autosaveModule(module);
            }, 1800);
        },

        async deleteModuleServer(module) {
            if (!module.id || !config.autosaveUrls?.moduleDelete) return;
            try {
                await fetch(`${config.autosaveUrls.moduleDelete}/${module.id}`, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                });
            } catch (error) {
                // Non-fatal: the full form save will clean up orphaned modules
                console.warn('[autosave] Module delete failed:', error.message);
            }
        },

        async deleteLessonServer(lesson) {
            if (!lesson.id || !config.autosaveUrls?.lessonDelete) return;
            try {
                const url = config.autosaveUrls.lessonDelete.replace('__LESSON_ID__', lesson.id);
                await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                });
            } catch (error) {
                console.warn('[autosave] Lesson delete failed:', error.message);
            }
        },

        // Flush queued saves after coming back online
        async flushPendingAutosaves() {
            const pending = Object.values(this.autosave.unsaved);
            for (const lesson of pending) {
                await this.autosaveLessonNow(lesson);
            }
        },

        // ── Platform ────────────────────────────────────────────────────────

        pickPlatform(name, color) {
            this.platform = name;
            this.platformColor = color;
            this.showSuggestions = false;
        },

        // ── Module helpers ──────────────────────────────────────────────────

        blankModule(sortOrder) {
            return {
                id: null,
                client_key: this.newModuleKey(sortOrder),
                title: sortOrder === 1 ? 'Core Training' : '',
                description: '',
                is_published: true,
                sort_order: sortOrder,
            };
        },

        blankLesson(sortOrder, moduleKey = null) {
            const module = this.moduleFromFilter(moduleKey)
                || this.modules[0]
                || this.blankModule(1);

            return {
                id: null,
                client_key: this.newLessonKey(sortOrder),
                course_id: this.courseId,
                course_module_id: module.id ?? '',
                module_key: module.client_key,
                module_title: module.title || 'Core Training',
                title: '',
                body: '',
                overview: '',
                steps: '',
                tips: '',
                safety_notes: '',
                resource_links: '',
                lesson_banner_image: '',
                lesson_banner_image_url: '',
                lesson_images: [],
                lesson_image_urls: [],
                content_blocks: [],
                content_blocks_enabled: true,
                new_block_type: 'text',
                is_published: true,
                video_url: '',
                bunny_video_id: '',
                bunny_library_id: '',
                bunny_video_title: '',
                bunny_thumbnail_url: '',
                bunny_upload_fingerprint: '',
                bunny_status: '',
                duration: '',
                pdf_url: '',
                presentation_url: '',
                sort_order: sortOrder,
            };
        },

        blankIntroVideo() {
            return {
                video_url: '',
                bunny_video_id: '',
                bunny_library_id: '',
                bunny_video_title: '',
                bunny_thumbnail_url: '',
                bunny_upload_fingerprint: '',
                bunny_status: '',
                duration: '',
            };
        },

        normalizedIntroVideo(video) {
            return {
                ...this.blankIntroVideo(),
                ...video,
            };
        },

        normalizedModules(modules, lessons = []) {
            const sourceModules = (modules ?? []).length > 0
                ? modules
                : this.modulesFromLessons(lessons);

            const normalized = sourceModules.map((module, index) => this.normalizedModule(module, index));

            return normalized.length > 0 ? normalized : [this.blankModule(1)];
        },

        modulesFromLessons(lessons) {
            const titles = [];

            (lessons ?? []).forEach((lesson) => {
                const title = (lesson.module_title || 'Core Training').trim();
                if (!titles.includes(title)) {
                    titles.push(title);
                }
            });

            return titles.map((title, index) => ({
                title,
                description: '',
                is_published: true,
                sort_order: index + 1,
            }));
        },

        normalizedModule(module, index) {
            const sortOrder = Number(module.sort_order ?? index + 1);
            const title = module.title || module.module_title || (sortOrder === 1 ? 'Core Training' : '');

            return {
                id: module.id ?? null,
                client_key: module.client_key ?? (module.id ? `module-${module.id}` : this.newModuleKey(sortOrder)),
                title,
                description: module.description ?? '',
                is_published: this.booleanValue(module.is_published, true),
                sort_order: sortOrder,
            };
        },

        normalizedLesson(lesson, index) {
            const moduleKey = this.moduleKeyForLesson(lesson);
            const normalized = {
                ...this.blankLesson(index + 1),
                ...lesson,
                module_key: moduleKey,
            };

            normalized.client_key = normalized.client_key || this.newLessonKey(index + 1);
            normalized.course_id = normalized.course_id ?? this.courseId;
            normalized.is_published = this.booleanValue(normalized.is_published, true);
            normalized.module_title = this.moduleTitleForKey(moduleKey);
            normalized.course_module_id = this.moduleIdForKey(moduleKey);
            normalized.content_blocks = (normalized.content_blocks ?? []).map((block, blockIndex) => this.normalizedContentBlock(block, blockIndex));
            normalized.content_blocks_enabled = true;
            normalized.new_block_type = normalized.new_block_type || 'text';

            return normalized;
        },

        // ── Content blocks ──────────────────────────────────────────────────

        blankContentBlock(sortOrder, type = 'text') {
            return {
                id: null,
                temp_id: this.newContentBlockKey(sortOrder),
                block_type: type,
                title: '',
                content: '',
                image_path: '',
                image_url: '',
                gallery_image_urls: [],
                gallery_captions: '',
                file_path: '',
                file_url: '',
                slide_images: [],
                button_label: '',
                bunny_video_id: '',
                bunny_library_id: '',
                bunny_video_title: '',
                bunny_thumbnail_url: '',
                bunny_upload_fingerprint: '',
                bunny_status: '',
                duration: '',
                presentation_url: '',
                sort_order: sortOrder,
            };
        },

        normalizedContentBlock(block, index) {
            const sortOrder = Number(block.sort_order ?? index + 1);
            const blockType = this.canonicalContentBlockType(block.block_type);

            return {
                ...this.blankContentBlock(sortOrder, blockType),
                ...block,
                temp_id: block.temp_id || this.newContentBlockKey(sortOrder),
                block_type: blockType,
                gallery_image_urls: block.gallery_image_urls ?? [],
                slide_images: block.slide_images ?? [],
                gallery_captions: block.gallery_captions ?? '',
                button_label: block.button_label ?? '',
                sort_order: sortOrder,
            };
        },

        canonicalContentBlockType(type) {
            const aliases = {
                heading: 'text',
                gallery: 'image',
                canva: 'presentation',
                pdf: 'pdf_resource',
                tip: 'text',
                tips: 'text',
                warning: 'text',
                safety: 'text',
                step: 'text',
                steps: 'text',
                divider: 'text',
            };
            const canonical = aliases[type] ?? type;

            return this.contentBlockTypes().includes(canonical) ? canonical : 'text';
        },

        contentBlockTypes() {
            return ['text', 'image', 'video', 'pdf_resource'];
        },

        blockTypeLabel(type) {
            return {
                text: 'Text',
                image: 'Image',
                video: 'Bunny video',
                pdf_resource: 'PDF',
                presentation: 'Presentation',
            }[type] || 'Text';
        },

        addLessonBlock(lessonIndex, type = 'text') {
            const lesson = this.lessons[lessonIndex];
            if (!lesson) {
                return;
            }

            lesson.content_blocks.push(this.blankContentBlock(lesson.content_blocks.length + 1, type));
            lesson.new_block_type = type;
            this.reorderLessonBlocks(lesson);
        },

        removeLessonBlock(lessonIndex, blockIndex) {
            const lesson = this.lessons[lessonIndex];
            if (!lesson) {
                return;
            }

            lesson.content_blocks.splice(blockIndex, 1);
            this.reorderLessonBlocks(lesson);
        },

        moveLessonBlock(lessonIndex, blockIndex, direction) {
            const lesson = this.lessons[lessonIndex];
            const newIndex = blockIndex + direction;
            if (!lesson || newIndex < 0 || newIndex >= lesson.content_blocks.length) {
                return;
            }

            const blocks = [...lesson.content_blocks];
            const [block] = blocks.splice(blockIndex, 1);
            blocks.splice(newIndex, 0, block);
            lesson.content_blocks = blocks;
            this.reorderLessonBlocks(lesson);
        },

        reorderLessonBlocks(lesson) {
            lesson.content_blocks = lesson.content_blocks.map((block, index) => ({
                ...block,
                temp_id: block.temp_id || this.newContentBlockKey(index + 1),
                sort_order: index + 1,
            }));
        },

        // ── Module resolution ───────────────────────────────────────────────

        moduleKeyForLesson(lesson) {
            if (lesson.course_module_id) {
                const moduleById = this.modules.find((module) => String(module.id) === String(lesson.course_module_id));
                if (moduleById) {
                    return moduleById.client_key;
                }
            }

            if (lesson.module_key && this.modules.some((module) => module.client_key === lesson.module_key)) {
                return lesson.module_key;
            }

            // Legacy drafts may only have a module title. Real saved lessons use course_module_id.
            if (lesson.module_title) {
                const moduleByTitle = this.modules.find((module) => module.title === lesson.module_title);
                if (moduleByTitle) {
                    return moduleByTitle.client_key;
                }
            }

            return this.modules[0]?.client_key ?? this.blankModule(1).client_key;
        },

        booleanValue(value, fallback = false) {
            if (value === undefined || value === null || value === '') {
                return fallback;
            }

            return value === true || value === 1 || value === '1' || value === 'true';
        },

        // ── Lesson CRUD ─────────────────────────────────────────────────────

        addLesson(moduleKey = null) {
            const lesson = this.blankLesson(this.lessons.length + 1, moduleKey);
            this.lessons.push(lesson);

            // Immediately autosave to get a server ID
            if (config.autosaveUrls?.lessonSave && this.autosave.isOnline) {
                this.$nextTick(() => this.autosaveLessonNow(lesson));
            } else {
                this.saveLocalDraft();
            }

            return this.lessonKey(lesson);
        },

        addLessonForModule(moduleIndex) {
            return this.addLesson(this.activeModuleKey(moduleIndex));
        },

        addLessonForFilter(filter, fallbackModule = 0) {
            const moduleKey = this.isAllLessonsFilter(filter)
                ? this.moduleKeyFromFilter(fallbackModule)
                : this.moduleKeyFromFilter(filter);

            return this.addLesson(moduleKey);
        },

        removeLesson(index) {
            const removed = this.lessons[index];
            this.lessons.splice(index, 1);
            this.lessons = this.lessons.map((lesson, i) => ({ ...lesson, sort_order: i + 1 }));

            // Delete from server if it has a DB id
            if (removed?.id) {
                this.deleteLessonServer(removed);
            }
            this.saveLocalDraft();
        },

        removeLessonForFilter(index, filter = 0) {
            this.removeLesson(index);

            return this.firstLessonKeyForFilter(filter);
        },

        // ── Module CRUD ─────────────────────────────────────────────────────

        addModule() {
            this.modules.push(this.blankModule(this.modules.length + 1));
            this.reorderModules();

            // Immediately autosave the new module to get a server ID
            const newModule = this.modules[this.modules.length - 1];
            if (config.autosaveUrls?.moduleSave && this.autosave.isOnline) {
                this.$nextTick(() => this.autosaveModule(newModule));
            } else {
                this.saveLocalDraft();
            }
        },

        removeModule(index) {
            const removed = this.modules[index];
            this.modules.splice(index, 1);

            if (this.modules.length === 0) {
                this.modules.push(this.blankModule(1));
            }

            const fallbackKey = this.modules[0].client_key;
            this.lessons = this.lessons.map((lesson) => {
                const belongedToRemoved = removed?.id
                    ? String(lesson.course_module_id || '') === String(removed.id)
                    : lesson.module_key === removed?.client_key;

                if (!belongedToRemoved) {
                    return lesson;
                }

                return this.lessonWithModule(lesson, fallbackKey);
            });
            this.reorderModules();

            // Delete from server if it has a DB id
            if (removed?.id) {
                this.deleteModuleServer(removed);
            }
            this.saveLocalDraft();
        },

        moveModule(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.modules.length) {
                return;
            }

            const modules = [...this.modules];
            const [module] = modules.splice(index, 1);
            modules.splice(newIndex, 0, module);
            this.modules = modules;
            this.reorderModules();
        },

        reorderModules() {
            this.modules = this.modules.map((module, index) => ({
                ...module,
                sort_order: index + 1,
            }));
        },

        moveLesson(globalIndex, direction, filter) {
            const lesson = this.lessons[globalIndex];
            if (!lesson) return;

            const filtered = this.lessonsForFilter(filter);
            const filteredIdx = filtered.findIndex((l) => this.lessonKey(l) === this.lessonKey(lesson));
            const newFilteredIdx = filteredIdx + direction;

            if (newFilteredIdx < 0 || newFilteredIdx >= filtered.length) return;

            const swapLesson = filtered[newFilteredIdx];
            const swapGlobalIdx = this.lessons.findIndex((l) => this.lessonKey(l) === this.lessonKey(swapLesson));

            const lessons = [...this.lessons];
            [lessons[globalIndex], lessons[swapGlobalIdx]] = [lessons[swapGlobalIdx], lessons[globalIndex]];
            this.lessons = lessons;
            this.reorderLessons();

            if (config.autosaveUrls?.lessonReorder && this.autosave.isOnline) {
                this.$nextTick(() => this.persistLessonOrder());
                return;
            }

            // Fallback for drafts/offline mode: persist both affected lessons when possible.
            const movedKey = this.lessonKey(lesson);
            const swappedKey = this.lessonKey(swapLesson);
            const moved = this.lessons.find((l) => this.lessonKey(l) === movedKey);
            const swapped = this.lessons.find((l) => this.lessonKey(l) === swappedKey);
            if (moved?.id) this.$nextTick(() => this.autosaveLessonNow(moved));
            if (swapped?.id) this.$nextTick(() => this.autosaveLessonNow(swapped));
        },

        reorderLessons() {
            this.lessons = this.lessons.map((lesson, index) => ({
                ...lesson,
                sort_order: index + 1,
            }));
        },

        lessonFilteredIndex(lesson, filter) {
            const filtered = this.lessonsForFilter(filter);
            return filtered.findIndex((l) => this.lessonKey(l) === this.lessonKey(lesson));
        },

        async persistLessonOrder() {
            if (!config.autosaveUrls?.lessonReorder || !this.autosave.isOnline) {
                this.saveLocalDraft();
                return;
            }

            const order = this.lessons
                .filter((lesson) => lesson.id)
                .map((lesson) => Number(lesson.id));

            if (!order.length) {
                this.saveLocalDraft();
                return;
            }

            try {
                this.setAutosaveStatus('saving', 'Saving lesson order...');

                const response = await fetch(config.autosaveUrls.lessonReorder, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({ order }),
                });

                await this.jsonResponse(response);
                this.setAutosaveStatus('saved', 'Lesson order saved.');
                this.saveLocalDraft();
            } catch (error) {
                this.setAutosaveStatus('error', error.message || 'Lesson order save failed.');
                this.saveLocalDraft();
            }
        },

        // ── Module/lesson linkage ───────────────────────────────────────────

        syncLessonModule(lesson) {
            Object.assign(lesson, this.lessonWithModule(lesson, lesson.module_key));
        },

        activeModuleKey(moduleIndex = 0) {
            return this.modules[moduleIndex]?.client_key
                ?? this.modules[0]?.client_key
                ?? this.blankModule(1).client_key;
        },

        moduleFromFilter(filter = 0) {
            if (filter && filter !== 'all' && this.modules.some((module) => module.client_key === filter)) {
                return this.modules.find((module) => module.client_key === filter) ?? null;
            }

            const numericIndex = Number(filter);
            if (Number.isInteger(numericIndex) && numericIndex >= 0 && numericIndex < this.modules.length) {
                return this.modules[numericIndex];
            }

            if (filter && filter !== 'all') {
                const moduleById = this.modules.find((module) => module.id && String(module.id) === String(filter));
                if (moduleById) {
                    return moduleById;
                }
            }

            return this.modules[0] ?? null;
        },

        moduleKeyFromFilter(filter = 0) {
            return this.moduleFromFilter(filter)?.client_key
                ?? this.activeModuleKey(0);
        },

        moduleLabel(moduleIndex = 0) {
            return `Module ${Number(moduleIndex) + 1}`;
        },

        selectedModuleName(moduleKeyOrIndex = 0) {
            const moduleKey = this.moduleKeyFromFilter(moduleKeyOrIndex);
            const moduleIndex = this.moduleIndexForKey(moduleKey);

            return this.modules[moduleIndex]?.title || `Module ${moduleIndex + 1}`;
        },

        moduleContextLabel(moduleKeyOrIndex = 0) {
            const moduleKey = this.moduleKeyFromFilter(moduleKeyOrIndex);
            const moduleIndex = this.moduleIndexForKey(moduleKey);
            const label = this.moduleLabel(moduleIndex);
            const title = this.selectedModuleName(moduleKey);

            return title && title !== label ? `${label} - ${title}` : label;
        },

        lessonFilterContext(filter = 0) {
            return this.isAllLessonsFilter(filter) ? 'All Lessons' : this.moduleContextLabel(filter);
        },

        lessonKey(lesson) {
            if (!lesson) {
                return null;
            }

            return lesson.id ? `lesson-${lesson.id}` : lesson.client_key;
        },

        lessonBelongsToModule(lesson, moduleKeyOrIndex = 0) {
            const module = this.moduleFromFilter(moduleKeyOrIndex);

            if (!lesson || !module) {
                return false;
            }

            if (module.id) {
                return String(lesson.course_module_id || '') === String(module.id);
            }

            return lesson.module_key === module.client_key;
        },

        isAllLessonsFilter(filter) {
            return filter === 'all';
        },

        lessonMatchesFilter(lesson, filter = 0) {
            return this.isAllLessonsFilter(filter) || this.lessonBelongsToModule(lesson, filter);
        },

        lessonsForModule(moduleKeyOrIndex = 0) {
            return this.lessons.filter((lesson) => this.lessonBelongsToModule(lesson, moduleKeyOrIndex));
        },

        lessonsForFilter(filter = 0) {
            return this.isAllLessonsFilter(filter)
                ? this.lessons
                : this.lessonsForModule(filter);
        },

        firstLessonKeyForFilter(filter = 0) {
            return this.lessonKey(this.lessonsForFilter(filter)[0]) ?? null;
        },

        moduleIndexForKey(moduleKey) {
            const index = this.modules.findIndex((module) => module.client_key === moduleKey);

            return index >= 0 ? index : 0;
        },

        moduleForLesson(lesson) {
            if (!lesson) {
                return null;
            }

            if (lesson.course_module_id) {
                const moduleById = this.modules.find((module) => module.id && String(module.id) === String(lesson.course_module_id));
                if (moduleById) {
                    return moduleById;
                }
            }

            if (lesson.module_key) {
                return this.modules.find((module) => module.client_key === lesson.module_key) ?? null;
            }

            return null;
        },

        moduleIndexForLesson(lesson) {
            const module = this.moduleForLesson(lesson);
            const index = module ? this.modules.findIndex((candidate) => candidate.client_key === module.client_key) : -1;

            return index >= 0 ? index : 0;
        },

        lessonNumberInModule(lesson) {
            const module = this.moduleForLesson(lesson);
            const moduleLessons = module
                ? this.lessons.filter((candidate) => this.lessonBelongsToModule(candidate, module.client_key))
                : [];
            const lessonKey = this.lessonKey(lesson);
            const index = moduleLessons.findIndex((candidate) => this.lessonKey(candidate) === lessonKey);

            return index >= 0 ? index + 1 : 1;
        },

        lessonTabLabel(lesson, filter = 0) {
            const lessonLabel = `Lesson ${this.lessonNumberInModule(lesson)}`;

            if (!this.isAllLessonsFilter(filter)) {
                return lessonLabel;
            }

            return `M${this.moduleIndexForLesson(lesson) + 1} - ${lessonLabel}`;
        },

        changeLessonModuleForFilter(lesson, filter = 0) {
            this.syncLessonModule(lesson);

            if (this.isAllLessonsFilter(filter) || this.lessonMatchesFilter(lesson, filter)) {
                this.lessonMoveNotice = null;

                return this.lessonKey(lesson);
            }

            this.lessonMoveNotice = `This lesson was moved to ${this.moduleLabel(this.moduleIndexForLesson(lesson))}.`;

            return this.firstLessonKeyForFilter(filter);
        },

        lessonWithModule(lesson, moduleKey) {
            const module = this.moduleFromFilter(moduleKey)
                || this.modules[0]
                || this.blankModule(1);

            return {
                ...lesson,
                module_key: module.client_key,
                course_module_id: module.id ?? '',
                module_title: module.title || 'Core Training',
            };
        },

        moduleIdForKey(moduleKey) {
            return this.moduleFromFilter(moduleKey)?.id ?? '';
        },

        moduleTitleForKey(moduleKey) {
            return this.moduleFromFilter(moduleKey)?.title || 'Core Training';
        },

        newModuleKey(sortOrder) {
            return `module-${Date.now()}-${sortOrder}-${Math.random().toString(36).slice(2, 8)}`;
        },

        newLessonKey(sortOrder) {
            return `lesson-new-${Date.now()}-${sortOrder}-${Math.random().toString(36).slice(2, 8)}`;
        },

        newContentBlockKey(sortOrder) {
            return `block-new-${Date.now()}-${sortOrder}-${Math.random().toString(36).slice(2, 8)}`;
        },

        // ── Bunny video picker ──────────────────────────────────────────────

        openBunnyPicker(index) {
            this.openBunnyPickerFor('lesson', index);
        },

        openBlockBunnyPicker(lessonIndex, blockIndex) {
            this.openBunnyPickerFor('lesson_block', lessonIndex, blockIndex);
        },

        openIntroBunnyPicker() {
            this.openBunnyPickerFor('intro');
        },

        openBunnyPickerFor(type, index = null, blockIndex = null) {
            this.bunnyTarget = { type, index, blockIndex };
            this.bunnyModalOpen = true;
            this.bunnyError = null;
            this.fetchBunnyVideos();
        },

        closeBunnyPicker() {
            this.bunnyModalOpen = false;
            this.bunnyTarget = null;
            this.bunnySearch = '';
            this.bunnyError = null;
        },

        async fetchBunnyVideos() {
            this.bunnyLoading = true;
            this.bunnyError = null;

            try {
                const url = new URL(config.bunnyVideosUrl, window.location.origin);
                if (this.bunnySearch) {
                    url.searchParams.set('search', this.bunnySearch);
                }

                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                const data = await this.jsonResponse(response);
                this.bunnyVideos = data.items ?? [];
            } catch (error) {
                this.bunnyError = error.message;
            } finally {
                this.bunnyLoading = false;
            }
        },

        selectBunnyVideo(video) {
            if (!this.bunnyTarget) {
                return;
            }

            this.applyBunnyVideoToTarget(this.bunnyTarget, video);
            this.closeBunnyPicker();
        },

        async uploadBunnyVideo(index, event) {
            return this.uploadBunnyVideoFor({ type: 'lesson', index }, event);
        },

        async uploadBlockBunnyVideo(lessonIndex, blockIndex, event) {
            return this.uploadBunnyVideoFor({ type: 'lesson_block', index: lessonIndex, blockIndex }, event);
        },

        async uploadIntroBunnyVideo(event) {
            return this.uploadBunnyVideoFor({ type: 'intro', index: null }, event);
        },

        async uploadBunnyVideoFor(target, event) {
            const file = event.target.files?.[0];
            event.target.value = '';

            if (!file) {
                return;
            }

            const videoTarget = this.videoTarget(target);
            const uploadKey = this.uploadKey(target);
            const title = this.videoTargetTitle(target, file);
            const fingerprint = `${file.name}:${file.size}:${file.lastModified}`;

            if (
                videoTarget.bunny_upload_fingerprint === fingerprint
                && videoTarget.bunny_video_id
                && this.uploadCompleted(uploadKey)
            ) {
                return;
            }

            this.setUpload(uploadKey, { progress: 0, status: 'Preparing Bunny upload...', error: null });

            try {
                const intentResponse = await fetch(config.bunnyUploadIntentUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({
                        title,
                        file_name: file.name,
                        file_size: file.size,
                        fingerprint,
                    }),
                });

                const intent = await this.jsonResponse(intentResponse);

                if (intent.duplicate && intent.video) {
                    this.applyBunnyVideoToTarget(target, intent.video, fingerprint);
                    this.setUpload(uploadKey, { progress: 100, status: 'Existing Bunny video reused.', error: null });
                    return;
                }

                this.setUpload(uploadKey, { progress: 1, status: 'Uploading directly to Bunny...', error: null });

                await this.uploadFileToBunnyTus(uploadKey, file, intent.upload, title);

                let video = intent.video;
                try {
                    video = await this.fetchBunnyVideo(intent.video.id);
                } catch (_) {
                    // Bunny can take a moment to expose fresh metadata. The created payload is enough for saving.
                }

                this.applyBunnyVideoToTarget(target, video, fingerprint);
                this.setUpload(uploadKey, { progress: 100, status: 'Upload complete. Save the course to keep this video.', error: null });
            } catch (error) {
                this.setUpload(uploadKey, { progress: this.uploads[uploadKey]?.progress ?? 0, status: 'Upload failed.', error: error.message });
            }
        },

        applyBunnyVideo(index, video, fingerprint = null) {
            this.applyBunnyVideoToTarget({ type: 'lesson', index }, video, fingerprint);
        },

        applyBunnyVideoToTarget(target, video, fingerprint = null) {
            if (target.type === 'intro') {
                this.introVideo = this.videoWithBunnyData(this.introVideo, video, fingerprint);

                return;
            }

            if (target.type === 'lesson_block') {
                const block = this.lessons[target.index]?.content_blocks?.[target.blockIndex];
                if (block) {
                    this.lessons[target.index].content_blocks[target.blockIndex] = this.videoWithBunnyData(block, video, fingerprint);
                }

                return;
            }

            const lesson = this.lessons[target.index];
            this.lessons[target.index] = this.videoWithBunnyData(lesson, video, fingerprint);
        },

        videoWithBunnyData(current, video, fingerprint = null) {
            return {
                ...current,
                video_url: video.embed_url ?? '',
                bunny_video_id: video.id ?? '',
                bunny_library_id: video.library_id ?? '',
                bunny_video_title: video.title ?? current.title ?? '',
                bunny_thumbnail_url: video.thumbnail_url ?? '',
                bunny_upload_fingerprint: fingerprint ?? current.bunny_upload_fingerprint ?? '',
                bunny_status: video.status ?? '',
                duration: video.duration ?? current.duration ?? '',
            };
        },

        clearIntroVideo() {
            this.introVideo = this.blankIntroVideo();
        },

        clearBlockBunnyVideo(lessonIndex, blockIndex) {
            const block = this.lessons[lessonIndex]?.content_blocks?.[blockIndex];
            if (!block) {
                return;
            }

            Object.assign(block, {
                bunny_video_id: '',
                bunny_library_id: '',
                bunny_video_title: '',
                bunny_thumbnail_url: '',
                bunny_upload_fingerprint: '',
                bunny_status: '',
                duration: '',
            });
        },

        videoTarget(target) {
            if (target.type === 'lesson_block') {
                return this.lessons[target.index]?.content_blocks?.[target.blockIndex] ?? {};
            }

            return target.type === 'intro'
                ? this.introVideo
                : this.lessons[target.index];
        },

        videoTargetTitle(target, file) {
            if (target.type === 'intro') {
                return document.getElementById('intro_title')?.value || file.name.replace(/\.[^/.]+$/, '');
            }

            if (target.type === 'lesson_block') {
                const block = this.lessons[target.index]?.content_blocks?.[target.blockIndex];

                return block?.title || this.lessons[target.index]?.title || file.name.replace(/\.[^/.]+$/, '');
            }

            return this.lessons[target.index]?.title || file.name.replace(/\.[^/.]+$/, '');
        },

        uploadKey(target) {
            if (target.type === 'lesson_block') {
                const lesson = this.lessons[target.index];
                const block = lesson?.content_blocks?.[target.blockIndex];

                return this.blockUploadKey(lesson, block);
            }

            return target.type === 'intro' ? 'intro' : target.index;
        },

        blockKey(block) {
            return block?.id ? `block-${block.id}` : block?.temp_id;
        },

        blockUploadKey(lesson, block) {
            if (Number.isInteger(lesson)) {
                const resolvedLesson = this.lessons[lesson];
                const resolvedBlock = resolvedLesson?.content_blocks?.[block];

                return this.blockUploadKey(resolvedLesson, resolvedBlock);
            }

            return `${this.lessonKey(lesson)}:${this.blockKey(block) || 'block'}`;
        },

        blockFileUploadKey(lesson, block) {
            if (Number.isInteger(lesson)) {
                const resolvedLesson = this.lessons[lesson];
                const resolvedBlock = resolvedLesson?.content_blocks?.[block];

                return this.blockFileUploadKey(resolvedLesson, resolvedBlock);
            }

            return `file:${this.blockUploadKey(lesson, block)}`;
        },

        async uploadBlockLocalFile(lessonIndex, blockIndex, event, type) {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }

            event.target.value = '';

            const lesson = this.lessons[lessonIndex];
            const blockKey = this.blockKey(lesson?.content_blocks?.[blockIndex]);
            const uploadKey = this.blockFileUploadKey(lesson, lesson?.content_blocks?.[blockIndex]);
            this.setUpload(uploadKey, { progress: 0, status: 'Uploading...', error: null });

            const formData = new FormData();
            formData.append('type', type);
            formData.append('file', file);

            try {
                await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();

                    // Store so cancelBlockUpload() can call xhr.abort()
                    if (!this.activeXhrs) this.activeXhrs = {};
                    this.activeXhrs[uploadKey] = xhr;

                    xhr.open('POST', config.blockFileUploadUrl);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken());

                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            const pct = Math.min(99, Math.round((e.loaded / e.total) * 100));
                            this.setUpload(uploadKey, { progress: pct, status: `Uploading... ${pct}%`, error: null });
                        }
                    };

                    xhr.onabort = () => {
                        delete this.activeXhrs[uploadKey];
                        reject(new Error('__cancelled__'));
                    };

                    xhr.onload = () => {
                        delete this.activeXhrs[uploadKey];
                        if (xhr.status >= 200 && xhr.status < 300) {
                            let data = {};
                            try { data = JSON.parse(xhr.responseText); } catch (_) {}

                            const currentLesson = this.lessons[lessonIndex];
                            const currentBlockIndex = currentLesson?.content_blocks?.findIndex((candidate) => this.blockKey(candidate) === blockKey) ?? -1;
                            const block = currentBlockIndex >= 0 ? currentLesson.content_blocks[currentBlockIndex] : null;
                            if (block) {
                                if (type === 'image') {
                                    currentLesson.content_blocks[currentBlockIndex].image_path = data.path ?? '';
                                    currentLesson.content_blocks[currentBlockIndex].image_url = data.url ?? '';
                                } else {
                                    currentLesson.content_blocks[currentBlockIndex].file_path = data.path ?? '';
                                    currentLesson.content_blocks[currentBlockIndex].file_url = data.url ?? '';
                                    currentLesson.content_blocks[currentBlockIndex].slide_images = data.slide_images ?? [];
                                }
                            }

                            this.setUpload(uploadKey, { progress: 100, status: 'Upload complete. Save the course to keep this file.', error: null });
                            resolve();
                        } else {
                            let message = 'Upload failed.';
                            try { message = JSON.parse(xhr.responseText)?.message || message; } catch (_) {}
                            reject(new Error(message));
                        }
                    };

                    xhr.onerror = () => {
                        delete this.activeXhrs[uploadKey];
                        reject(new Error('Network error during upload.'));
                    };

                    xhr.send(formData);
                });
            } catch (error) {
                if (error.message === '__cancelled__') {
                    this.setUpload(uploadKey, { progress: 0, status: 'Upload cancelled.', error: null });
                } else {
                    this.setUpload(uploadKey, {
                        progress: this.uploads[uploadKey]?.progress ?? 0,
                        status: 'Upload failed.',
                        error: error.message,
                    });
                }
            }
        },

        cancelBlockUpload(uploadKey) {
            this.activeXhrs?.[uploadKey]?.abort();
        },

        // ── Preview / misc ──────────────────────────────────────────────────

        lessonPreviewUrl(lesson) {
            if (!lesson?.id || !config.lessonPreviewUrlTemplate) {
                return '#';
            }

            return config.lessonPreviewUrlTemplate.replace('__LESSON_ID__', encodeURIComponent(lesson.id));
        },

        uploadCompleted(uploadKey) {
            return this.uploads[uploadKey]?.progress === 100 && !this.uploads[uploadKey]?.error;
        },

        async fetchBunnyVideo(videoId) {
            const url = config.bunnyVideoUrlTemplate.replace('__VIDEO_ID__', encodeURIComponent(videoId));
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
            });
            const data = await this.jsonResponse(response);

            return data.video;
        },

        // ── TUS upload ──────────────────────────────────────────────────────

        async uploadFileToBunnyTus(index, file, upload, title) {
            const authHeaders = {
                AuthorizationSignature: upload.signature,
                AuthorizationExpire: String(upload.expires_at),
                VideoId: upload.video_id,
                LibraryId: String(upload.library_id),
            };

            const createResponse = await fetch(upload.endpoint, {
                method: 'POST',
                headers: {
                    ...authHeaders,
                    'Tus-Resumable': '1.0.0',
                    'Upload-Length': String(file.size),
                    'Upload-Metadata': this.tusMetadata({
                        filetype: file.type || 'application/octet-stream',
                        title,
                    }),
                },
            });

            if (!createResponse.ok) {
                throw new Error(`Bunny upload session failed (${createResponse.status}).`);
            }

            const uploadLocationHeader = createResponse.headers.get('Location');
            if (!uploadLocationHeader) {
                throw new Error('Bunny did not return an upload location.');
            }

            const uploadLocation = this.absoluteTusUploadLocation(upload.endpoint, uploadLocationHeader);

            await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('PATCH', uploadLocation);
                xhr.setRequestHeader('Tus-Resumable', '1.0.0');
                xhr.setRequestHeader('Upload-Offset', '0');
                xhr.setRequestHeader('Content-Type', 'application/offset+octet-stream');
                Object.entries(authHeaders).forEach(([key, value]) => xhr.setRequestHeader(key, value));

                xhr.upload.onprogress = (event) => {
                    if (event.lengthComputable) {
                        const progress = Math.max(1, Math.min(99, Math.round((event.loaded / event.total) * 100)));
                        this.setUpload(index, { progress, status: `Uploading directly to Bunny... ${progress}%`, error: null });
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve();
                    } else {
                        reject(new Error(`Bunny upload failed (${xhr.status}).`));
                    }
                };
                xhr.onerror = () => reject(new Error('Network error while uploading to Bunny.'));
                xhr.send(file);
            });
        },

        absoluteTusUploadLocation(endpoint, location) {
            if (/^https?:\/\//i.test(location)) {
                return location;
            }

            const baseEndpoint = endpoint.endsWith('/') ? endpoint : `${endpoint}/`;

            return new URL(location, baseEndpoint).toString();
        },

        tusMetadata(metadata) {
            return Object.entries(metadata)
                .filter(([, value]) => value !== null && value !== undefined && value !== '')
                .map(([key, value]) => `${key} ${this.base64(String(value))}`)
                .join(',');
        },

        base64(value) {
            return btoa(unescape(encodeURIComponent(value)));
        },

        // ── Upload state ────────────────────────────────────────────────────

        setUpload(index, state) {
            this.uploads = {
                ...this.uploads,
                [index]: state,
            };
        },

        // ── Utilities ───────────────────────────────────────────────────────

        /**
         * Serialize ALL lesson content block data from canonical JS state into
         * hidden form inputs right before the form is submitted.
         *
         * This replaces the previous approach of relying on x-bind:value hidden
         * inputs scattered through the template, which could be deferred or stale
         * for non-active lesson panels.  By injecting from this.lessons at submit
         * time we guarantee that every block's media fields (id, image_path,
         * file_path, bunny_video_id, slide_images, etc.) always reflect the true
         * JS state, regardless of which lesson tab is active or whether blocks are
         * collapsed.
         */
        rebuildBlockHiddenInputs(form) {
            // Remove any previously injected inputs from an earlier call (e.g. if
            // the user hits submit twice without a page reload).
            form.querySelectorAll('input[data-pd-block-serial]').forEach((el) => el.remove());

            const inject = (name, value) => {
                const el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                el.value = (value === null || value === undefined) ? '' : String(value);
                el.dataset.pdBlockSerial = '1';
                form.appendChild(el);
            };

            this.lessons.forEach((lesson, lessonIndex) => {
                const blocks = lesson.content_blocks ?? [];

                // Authoritative block count — supersedes the template's x-bind version.
                inject(`lessons[${lessonIndex}][_content_block_count]`, blocks.length);

                blocks.forEach((block, blockIndex) => {
                    const p = `lessons[${lessonIndex}][content_blocks][${blockIndex}]`;

                    // Identity & ordering
                    inject(`${p}[id]`, block.id || '');
                    inject(`${p}[sort_order]`, blockIndex + 1);

                    // Block type (also present as a <select> — injecting here ensures
                    // it is always correct even if the select binding is deferred).
                    inject(`${p}[block_type]`, block.block_type || 'text');

                    // Image block
                    inject(`${p}[image_path]`, block.image_path || '');

                    // PDF / Presentation block
                    inject(`${p}[file_path]`, block.file_path || '');
                    inject(`${p}[presentation_url]`, block.presentation_url || '');

                    // Slide images (presentation)
                    (block.slide_images ?? []).forEach((slideImage, si) => {
                        if (slideImage) {
                            inject(`${p}[slide_images][${si}]`, slideImage);
                        }
                    });

                    // Video block (Bunny)
                    inject(`${p}[bunny_video_id]`, block.bunny_video_id || '');
                    inject(`${p}[bunny_library_id]`, block.bunny_library_id || '');
                    inject(`${p}[bunny_video_title]`, block.bunny_video_title || '');
                    inject(`${p}[bunny_thumbnail_url]`, block.bunny_thumbnail_url || '');
                    inject(`${p}[bunny_upload_fingerprint]`, block.bunny_upload_fingerprint || '');
                    inject(`${p}[bunny_status]`, block.bunny_status || '');
                    inject(`${p}[duration]`, block.duration || '');
                });
            });
        },

        debugCourseSubmit(form) {
            if (!import.meta.env.DEV) return;
            try {
                console.group('[ParadiseDollz] Course lesson flow submit');
                console.table(this.modules.map((module, moduleIndex) => ({
                    moduleIndex,
                    id: module.id ?? null,
                    tempId: module.client_key ?? null,
                    title: module.title ?? '',
                })));
                this.lessons.forEach((lesson, lessonIndex) => {
                    console.group(`lesson ${lessonIndex + 1}: ${lesson.title || '(untitled)'}`);
                    console.log({
                        lessonIndex,
                        id: lesson.id ?? null,
                        tempId: lesson.client_key ?? null,
                        moduleId: lesson.course_module_id ?? null,
                        moduleTempId: lesson.module_key ?? null,
                    });
                    console.table((lesson.content_blocks ?? []).map((block, blockIndex) => ({
                        blockIndex,
                        id: block.id ?? null,
                        tempId: block.temp_id ?? null,
                        type: block.block_type ?? null,
                        position: blockIndex + 1,
                        content: block.content ?? '',
                        file_url: block.file_url ?? '',
                        file_path: block.file_path ?? '',
                        image_url: block.image_url ?? '',
                        image_path: block.image_path ?? '',
                        video_url: block.video_url ?? '',
                        bunny_video_id: block.bunny_video_id ?? '',
                        presentation_url: block.presentation_url ?? '',
                        slide_images: Array.isArray(block.slide_images) ? block.slide_images.join(', ') : '',
                        new_file_selected: false,
                        remove_media: !!block.remove_media,
                    })));
                    console.groupEnd();
                });

                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    console.log('[ParadiseDollz] FormData', key, value instanceof File ? {
                        fileName: value.name,
                        size: value.size,
                        type: value.type,
                    } : value);
                }
                console.groupEnd();
            } catch (error) {
                console.warn('[ParadiseDollz] Course submit debug failed', error);
            }
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        },

        async jsonResponse(response) {
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message ?? `Request failed (${response.status}).`);
            }

            return data;
        },
    };
};
