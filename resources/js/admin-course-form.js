window.adminCourseForm = function adminCourseForm(config) {
    return {
        platform: config.platform ?? '',
        platformColor: config.platformColor ?? '#C9A96E',
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

        blankLesson(sortOrder) {
            const module = this.modules[0] ?? this.blankModule(1);

            return {
                id: null,
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

            normalized.is_published = this.booleanValue(normalized.is_published, true);
            normalized.module_title = this.moduleTitleForKey(moduleKey);
            normalized.course_module_id = this.moduleIdForKey(moduleKey);

            return normalized;
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

        addLesson() {
            this.lessons.push(this.blankLesson(this.lessons.length + 1));
        },

        removeLesson(index) {
            this.lessons.splice(index, 1);
            this.lessons = this.lessons.map((lesson, i) => ({ ...lesson, sort_order: i + 1 }));
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

        openBunnyPicker(index) {
            this.openBunnyPickerFor('lesson', index);
        },

        openIntroBunnyPicker() {
            this.openBunnyPickerFor('intro');
        },

        openBunnyPickerFor(type, index = null) {
            this.bunnyTarget = { type, index };
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

        videoTarget(target) {
            return target.type === 'intro'
                ? this.introVideo
                : this.lessons[target.index];
        },

        videoTargetTitle(target, file) {
            if (target.type === 'intro') {
                return document.getElementById('intro_title')?.value || file.name.replace(/\.[^/.]+$/, '');
            }

            return this.lessons[target.index]?.title || file.name.replace(/\.[^/.]+$/, '');
        },

        uploadKey(target) {
            return target.type === 'intro' ? 'intro' : target.index;
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
