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

        init() {
            this.introVideo = this.normalizedIntroVideo(config.introVideo ?? {});
            this.modules = this.normalizedModules(config.modules ?? [], config.lessons ?? []);
            this.lessons = (config.lessons ?? []).map((lesson, index) => this.normalizedLesson(lesson, index));
        },

        pickPlatform(name, color) {
            this.platform = name;
            this.platformColor = color;
            this.showSuggestions = false;
        },

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
            const module = this.modules.find((candidate) => candidate.client_key === moduleKey)
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

        blankContentBlock(sortOrder, type = 'text') {
            return {
                id: null,
                block_type: type,
                title: '',
                content: '',
                image_path: '',
                image_url: '',
                gallery_image_urls: [],
                gallery_captions: '',
                file_path: '',
                file_url: '',
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
                block_type: blockType,
                gallery_image_urls: block.gallery_image_urls ?? [],
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
            return ['text', 'image', 'video', 'pdf_resource', 'presentation'];
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
                sort_order: index + 1,
            }));
        },

        moduleKeyForLesson(lesson) {
            if (lesson.module_key && this.modules.some((module) => module.client_key === lesson.module_key)) {
                return lesson.module_key;
            }

            if (lesson.course_module_id) {
                const moduleById = this.modules.find((module) => String(module.id) === String(lesson.course_module_id));
                if (moduleById) {
                    return moduleById.client_key;
                }
            }

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

        addLesson(moduleKey = null) {
            const lesson = this.blankLesson(this.lessons.length + 1, moduleKey);
            this.lessons.push(lesson);

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
            this.lessons.splice(index, 1);
            this.lessons = this.lessons.map((lesson, i) => ({ ...lesson, sort_order: i + 1 }));
        },

        removeLessonForFilter(index, filter = 0) {
            this.removeLesson(index);

            return this.firstLessonKeyForFilter(filter);
        },

        addModule() {
            this.modules.push(this.blankModule(this.modules.length + 1));
            this.reorderModules();
        },

        removeModule(index) {
            const removed = this.modules[index];
            this.modules.splice(index, 1);

            if (this.modules.length === 0) {
                this.modules.push(this.blankModule(1));
            }

            const fallbackKey = this.modules[0].client_key;
            this.lessons = this.lessons.map((lesson) => {
                if (lesson.module_key !== removed.client_key) {
                    return lesson;
                }

                return this.lessonWithModule(lesson, fallbackKey);
            });
            this.reorderModules();
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

        syncLessonModule(lesson) {
            Object.assign(lesson, this.lessonWithModule(lesson, lesson.module_key));
        },

        activeModuleKey(moduleIndex = 0) {
            return this.modules[moduleIndex]?.client_key
                ?? this.modules[0]?.client_key
                ?? this.blankModule(1).client_key;
        },

        moduleKeyFromFilter(filter = 0) {
            if (filter && filter !== 'all' && this.modules.some((module) => module.client_key === filter)) {
                return filter;
            }

            const numericIndex = Number(filter);
            if (Number.isInteger(numericIndex) && numericIndex >= 0 && numericIndex < this.modules.length) {
                return this.activeModuleKey(numericIndex);
            }

            return this.activeModuleKey(0);
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
            return lesson.module_key === this.moduleKeyFromFilter(moduleKeyOrIndex);
        },

        isAllLessonsFilter(filter) {
            return filter === 'all';
        },

        lessonMatchesFilter(lesson, filter = 0) {
            return this.isAllLessonsFilter(filter) || this.lessonBelongsToModule(lesson, filter);
        },

        lessonsForModule(moduleKeyOrIndex = 0) {
            const moduleKey = this.moduleKeyFromFilter(moduleKeyOrIndex);

            return this.lessons.filter((lesson) => lesson.module_key === moduleKey);
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

        lessonNumberInModule(lesson) {
            const moduleLessons = this.lessons.filter((candidate) => candidate.module_key === lesson.module_key);
            const lessonKey = this.lessonKey(lesson);
            const index = moduleLessons.findIndex((candidate) => this.lessonKey(candidate) === lessonKey);

            return index >= 0 ? index + 1 : 1;
        },

        lessonTabLabel(lesson, filter = 0) {
            const lessonLabel = `Lesson ${this.lessonNumberInModule(lesson)}`;

            if (!this.isAllLessonsFilter(filter)) {
                return lessonLabel;
            }

            return `M${this.moduleIndexForKey(lesson.module_key) + 1} - ${lessonLabel}`;
        },

        changeLessonModuleForFilter(lesson, filter = 0) {
            this.syncLessonModule(lesson);

            if (this.isAllLessonsFilter(filter) || this.lessonMatchesFilter(lesson, filter)) {
                this.lessonMoveNotice = null;

                return this.lessonKey(lesson);
            }

            this.lessonMoveNotice = `This lesson was moved to ${this.moduleLabel(this.moduleIndexForKey(lesson.module_key))}.`;

            return this.firstLessonKeyForFilter(filter);
        },

        lessonWithModule(lesson, moduleKey) {
            return {
                ...lesson,
                module_key: moduleKey,
                course_module_id: this.moduleIdForKey(moduleKey),
                module_title: this.moduleTitleForKey(moduleKey),
            };
        },

        moduleIdForKey(moduleKey) {
            return this.modules.find((module) => module.client_key === moduleKey)?.id ?? '';
        },

        moduleTitleForKey(moduleKey) {
            return this.modules.find((module) => module.client_key === moduleKey)?.title || 'Core Training';
        },

        newModuleKey(sortOrder) {
            return `module-${Date.now()}-${sortOrder}-${Math.random().toString(36).slice(2, 8)}`;
        },

        newLessonKey(sortOrder) {
            return `lesson-new-${Date.now()}-${sortOrder}-${Math.random().toString(36).slice(2, 8)}`;
        },

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
                return this.blockUploadKey(target.index, target.blockIndex);
            }

            return target.type === 'intro' ? 'intro' : target.index;
        },

        blockUploadKey(lessonIndex, blockIndex) {
            return `lesson-${lessonIndex}-block-${blockIndex}`;
        },

        blockFileUploadKey(lessonIndex, blockIndex) {
            return `block-file-${lessonIndex}-${blockIndex}`;
        },

        async uploadBlockLocalFile(lessonIndex, blockIndex, event, type) {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }

            event.target.value = '';

            const uploadKey = this.blockFileUploadKey(lessonIndex, blockIndex);
            this.setUpload(uploadKey, { progress: 0, status: 'Uploading...', error: null });

            const formData = new FormData();
            formData.append('type', type);
            formData.append('file', file);

            try {
                await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', config.blockFileUploadUrl);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken());

                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            const pct = Math.min(99, Math.round((e.loaded / e.total) * 100));
                            this.setUpload(uploadKey, { progress: pct, status: `Uploading... ${pct}%`, error: null });
                        }
                    };

                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            let data = {};
                            try { data = JSON.parse(xhr.responseText); } catch (_) {}

                            const block = this.lessons[lessonIndex]?.content_blocks?.[blockIndex];
                            if (block) {
                                if (type === 'image') {
                                    this.lessons[lessonIndex].content_blocks[blockIndex].image_path = data.path ?? '';
                                    this.lessons[lessonIndex].content_blocks[blockIndex].image_url = data.url ?? '';
                                } else {
                                    this.lessons[lessonIndex].content_blocks[blockIndex].file_path = data.path ?? '';
                                    this.lessons[lessonIndex].content_blocks[blockIndex].file_url = data.url ?? '';
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

                    xhr.onerror = () => reject(new Error('Network error during upload.'));
                    xhr.send(formData);
                });
            } catch (error) {
                this.setUpload(uploadKey, {
                    progress: this.uploads[uploadKey]?.progress ?? 0,
                    status: 'Upload failed.',
                    error: error.message,
                });
            }
        },

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

        setUpload(index, state) {
            this.uploads = {
                ...this.uploads,
                [index]: state,
            };
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
