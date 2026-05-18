@php
    $outlinePath = old('course_outline_url', $course->course_outline_url ?? '');
    $outlineUrl = null;
    $outlineFileName = null;

    if (filled($outlinePath)) {
        $pathPart = parse_url($outlinePath, PHP_URL_PATH) ?: $outlinePath;
        $outlineFileName = basename(str_replace('\\', '/', rawurldecode($pathPart))) ?: __('Saved file');
        $outlineUrl = preg_match('/^https?:\/\//', $outlinePath)
            ? $outlinePath
            : route('admin.academy-files.show', ['path' => $outlinePath]);
    }
@endphp

<input type="hidden" name="course_outline_url" value="{{ $outlinePath }}">

<div
    x-data="{ drag: false, fileLabel: '' }"
    @dragover.prevent="drag = true"
    @dragleave.prevent="drag = false"
    @drop.prevent="
        drag = false;
        const files = $event.dataTransfer?.files;
        if (files?.length) {
            $refs.fileInput.files = files;
            fileLabel = files[0].name;
        }
    "
>
    @if ($outlineUrl)
        <div x-show="!fileLabel" class="mb-2 rounded-lg border border-white/[0.06] bg-[#08080f] px-3 py-2">
            <p class="text-[0.62rem] uppercase tracking-[0.14em] text-boss-ivory/24">{{ __('Current saved file') }}</p>
            <a href="{{ $outlineUrl }}" target="_blank" rel="noopener noreferrer" class="mt-1 block truncate text-[0.78rem] text-boss-gold hover:text-boss-gold-light">
                {{ $outlineFileName }}
            </a>
        </div>
    @endif

    <label
        for="course_outline_upload"
        class="flex min-h-[7rem] cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed px-4 py-5 text-center transition-all duration-200"
        :class="drag ? 'border-boss-gold/70 bg-boss-gold/[0.07]' : (fileLabel ? 'border-boss-gold/30 bg-boss-gold/[0.03]' : 'border-white/[0.10] bg-white/[0.025] hover:border-boss-gold/30 hover:bg-white/[0.035]')"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
            class="h-6 w-6 transition-colors duration-200"
            :class="drag ? 'text-boss-gold/80' : (fileLabel ? 'text-boss-gold/50' : 'text-boss-ivory/20')">
            <path d="M12 15V3m0 0l-4 4m4-4 4 4"/>
            <path d="M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2"/>
        </svg>
        <span class="text-[0.58rem] font-semibold uppercase tracking-[0.15em] transition-colors duration-200" :class="drag ? 'text-boss-gold' : (fileLabel ? 'text-boss-gold/70' : 'text-boss-gold/55')">
            <span x-text="fileLabel ? '{{ __('FILE SELECTED') }}' : '{{ __('DROP FILE HERE') }}'"></span>
        </span>
        <span class="text-[0.75rem] text-boss-ivory/42">{{ __('Drag and drop, or click to browse') }}</span>
        <span class="max-w-full truncate text-[0.65rem] text-boss-ivory/28" x-text="fileLabel || @js($outlineFileName ? __('Current: :file', ['file' => $outlineFileName]) : __('No file selected'))"></span>
        <span class="mt-0.5 text-[0.58rem] text-boss-ivory/18">{{ __('Upload PDF, DOC, DOCX, PPT, or PPTX') }}</span>
        <input
            type="file"
            id="course_outline_upload"
            name="course_outline_upload"
            accept=".pdf,.doc,.docx,.ppt,.pptx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation"
            class="sr-only"
            x-ref="fileInput"
            @change="const files = $event.target.files; fileLabel = files.length ? files[0].name : '';"
        >
    </label>
</div>

<p class="mt-1.5 text-[0.62rem] text-boss-ivory/20">{{ __('Upload a local guide file. Bunny is only used for videos.') }}</p>
<x-input-error class="mt-2" :messages="$errors->get('course_outline_upload')" />
<x-input-error class="mt-2" :messages="$errors->get('course_outline_url')" />
