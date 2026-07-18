<x-dynamic-component :component="$user->isAdmin() ? 'admin-layout' : ($user->isChatter() ? 'chatter-layout' : 'member-layout')">
@php
    $profilePhotoUrl = $user->profilePhotoUrl();
    $isAdmin         = $user->isAdmin();
    $roleLabel       = $isAdmin ? __('Administrator') : ($user->isChatter() ? __('Chatter') : __('Paradise Dolls Member'));
    $siteTheme       = \App\Models\SiteSetting::get('theme', ['mode'=>'dark','primary'=>'#EEB4C3','primaryLight'=>'#F3C3CF','preset'=>'pink-light']);
@endphp

<div class="space-y-5 text-boss-ivory">

    {{-- ── Page header ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="pd-kicker">{{ __('Account') }}</p>
            <h1 class="mt-1 font-display text-[clamp(1.7rem,3vw,2.4rem)] font-semibold leading-tight text-boss-ivory">
                {{ __('My Profile') }}
            </h1>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full border border-[rgba(238,180,195,0.22)] bg-[rgba(238,180,195,0.07)] px-3.5 py-1.5 text-[0.63rem] uppercase tracking-[0.15em] text-[rgba(238,180,195,0.82)]">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            {{ __('Account active') }}
        </span>
    </div>

    {{-- ── Profile hero card: avatar · info · actions ──────────────── --}}
    <section
        x-data="{
            previewUrl: @js($profilePhotoUrl),
            selected: false,
            fileName: '',
            error: '',
            saving: false,
            maxBytes: 4096 * 1024,
            allowedTypes: ['image/jpeg', 'image/png', 'image/webp'],
            setPhoto(event) {
                const file = event.target.files?.[0];
                this.error = '';

                if (!file) {
                    this.clearPickedFile();
                    return;
                }

                if (!this.allowedTypes.includes(file.type)) {
                    this.clearPickedFile();
                    this.error = @js(__('Please upload a JPG, PNG, or WEBP image.'));
                    return;
                }

                if (file.size > this.maxBytes) {
                    this.clearPickedFile();
                    this.error = @js(__('This photo is too large. Please choose an image under 4 MB.'));
                    return;
                }

                if (this.previewUrl && this.selected) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = URL.createObjectURL(file);
                this.fileName   = file.name;
                this.selected   = true;
            },
            clearPickedFile() {
                if (this.previewUrl && this.selected) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = @js($profilePhotoUrl);
                this.fileName   = '';
                this.selected   = false;
                if (this.$refs.photoInput) this.$refs.photoInput.value = '';
            },
            resetSelection() {
                this.error = '';
                this.clearPickedFile();
            },
        }"
        class="relative overflow-hidden rounded-2xl border border-white/[0.07] pd-profile-card-bg"
    >
        <div class="pd-card-shine absolute inset-x-0 top-0 h-px"></div>

        <form
            method="POST"
            action="{{ route('profile.photo.update') }}"
            enctype="multipart/form-data"
            @submit="saving = true"
            class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:gap-6 sm:p-6 lg:p-7"
        >
            @csrf

            {{-- Avatar --}}
            <label for="profile-photo-input"
                class="group relative mx-auto h-[84px] w-[84px] shrink-0 cursor-pointer rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-boss-gold sm:mx-0"
                :title="previewUrl ? '{{ __('Change photo') }}' : '{{ __('Upload photo') }}'">
                <div class="absolute inset-0 rounded-2xl p-[2px]"
                     :class="selected ? 'bg-gradient-to-br from-boss-gold to-[#C4687A]' : 'bg-gradient-to-br from-[rgba(238,180,195,0.4)] to-[rgba(196,104,122,0.2)]'">
                    <div class="pd-avatar-inner flex h-full w-full items-center justify-center rounded-[14px]">
                        <span class="font-display text-2xl font-semibold text-boss-gold">{{ $user->initials() }}</span>
                    </div>
                </div>
                <img x-show="previewUrl" :src="previewUrl" alt=""
                     class="absolute inset-0 h-full w-full rounded-2xl object-cover" x-on:error="previewUrl = null">
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-0.5 rounded-2xl bg-black/55 opacity-0 backdrop-blur-sm transition-opacity group-hover:opacity-100">
                    <svg viewBox="0 0 20 20" class="h-5 w-5 text-white" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-[0.55rem] font-semibold uppercase tracking-widest text-white/80">{{ __('Edit') }}</span>
                </div>
                <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-[#171016] bg-emerald-400"></span>
            </label>

            {{-- Info --}}
            <div class="min-w-0 flex-1 text-center sm:text-left">
                <h2 class="truncate font-display text-xl font-semibold text-boss-ivory sm:text-2xl">{{ $user->name }}</h2>
                <p class="mt-0.5 truncate text-sm text-boss-ivory/40">{{ $user->email }}</p>
                <div class="pd-role-badge mt-2.5 inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[0.62rem] uppercase tracking-[0.13em]">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    {{ $roleLabel }}
                </div>
            </div>

            {{-- Photo actions --}}
            <div class="flex flex-wrap items-center justify-center gap-2 sm:flex-col sm:flex-nowrap sm:items-end sm:justify-start">
                <label for="profile-photo-input"
                    class="relative inline-flex cursor-pointer items-center gap-1.5 overflow-hidden rounded-lg border border-white/[0.09] bg-white/[0.04] px-3.5 py-2 text-[0.72rem] text-boss-ivory/55 transition-all hover:border-[rgba(238,180,195,0.28)] hover:bg-[rgba(238,180,195,0.08)] hover:text-boss-gold">
                    <input
                        id="profile-photo-input"
                        type="file"
                        name="profile_photo"
                        accept="image/jpeg,image/png,image/webp"
                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                        x-ref="photoInput"
                        @change="setPhoto($event)"
                    >
                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.5]">
                        <path d="M8 2v8M5 5l3-3 3 3"/><path d="M3 11v1a2 2 0 002 2h6a2 2 0 002-2v-1"/>
                    </svg>
                    <span x-text="previewUrl ? '{{ __('Change Photo') }}' : '{{ __('Upload Photo') }}'"></span>
                </label>
                <button type="submit" x-show="selected" x-cloak :disabled="saving"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-[rgba(238,180,195,0.33)] bg-[rgba(238,180,195,0.11)] px-3.5 py-2 text-[0.72rem] text-boss-gold transition-all hover:bg-[rgba(238,180,195,0.18)] disabled:opacity-50">
                    <svg x-show="saving" class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12"/>
                    </svg>
                    <span x-text="saving ? '{{ __('Saving...') }}' : '{{ __('Save Photo') }}'"></span>
                </button>
                <button type="button" x-show="selected" x-cloak @click="resetSelection()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.07] bg-white/[0.03] px-3.5 py-2 text-[0.72rem] text-boss-ivory/38 transition-all hover:text-boss-ivory">
                    {{ __('Cancel') }}
                </button>
                @if ($profilePhotoUrl || filled($user->profile_photo_path))
                    <button type="submit" name="remove_profile_photo" value="1"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-400/13 bg-red-400/[0.04] px-3.5 py-2 text-[0.72rem] text-red-300/45 transition-all hover:border-red-400/22 hover:bg-red-400/[0.07] hover:text-red-200">
                        {{ __('Remove Photo') }}
                    </button>
                @endif
                <p class="w-full text-center text-[0.61rem] text-boss-ivory/22 sm:text-right">
                    <span x-show="!selected">{{ __('JPG, PNG, WEBP - 4 MB max') }}</span>
                    <span x-show="selected" x-cloak x-text="fileName" class="text-[rgba(238,180,195,0.45)]"></span>
                </p>
                <p x-show="selected" x-cloak class="w-full text-center text-[0.62rem] font-medium text-boss-gold/75 sm:text-right">
                    {{ __('Tap Save Photo to finish.') }}
                </p>
                <p x-show="error" x-cloak x-text="error" class="w-full text-center text-[0.68rem] text-red-300 sm:text-right"></p>
                <p x-show="error" x-cloak class="w-full text-center text-[0.61rem] text-boss-ivory/35 sm:text-right">
                    {{ __('If the photo picker or upload fails, please try Safari on iPhone or Chrome on Android.') }}
                </p>
                <x-input-error :messages="$errors->get('profile_photo')" />
                @if ($errors->has('profile_photo'))
                    <p class="w-full text-center text-[0.61rem] text-boss-ivory/35 sm:text-right">
                        {{ __('If the photo picker or upload fails, please try Safari on iPhone or Chrome on Android.') }}
                    </p>
                @endif
            </div>
        </form>
    </section>

    {{-- ── Settings grid: Profile Details + Password & Security ────── --}}
    <div class="grid gap-5 lg:grid-cols-2">

        {{-- Profile Details --}}
        <div class="overflow-hidden rounded-2xl border border-white/[0.07] pd-inner-card-bg">
            <div class="flex items-center gap-3 border-b border-white/[0.05] px-5 py-4 sm:px-6">
                <span class="pd-profile-icon-badge flex h-8 w-8 shrink-0 items-center justify-center rounded-xl">
                    <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.6]">
                        <circle cx="8" cy="5.5" r="2.8"/>
                        <path d="M2.5 14c0-3 2.5-5.5 5.5-5.5s5.5 2.5 5.5 5.5"/>
                    </svg>
                </span>
                <div>
                    <p class="font-display text-[0.93rem] font-semibold text-boss-ivory">{{ __('Profile Details') }}</p>
                    <p class="text-[0.61rem] text-boss-ivory/33">{{ __('Your display name and email') }}</p>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- Password & Security --}}
        <div class="overflow-hidden rounded-2xl border border-white/[0.07] pd-inner-card-bg">
            @include('profile.partials.update-password-form')
        </div>

    </div>

    {{-- ── Paradise Theme Customizer (admin only) ─────────────────── --}}
    @if($isAdmin)
    <div class="overflow-hidden rounded-2xl border border-white/[0.07] pd-inner-card-bg"
         x-data="pdThemeCustomizer()"
         x-init="init()">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-3 border-b border-white/[0.05] px-5 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <span class="pd-profile-icon-badge flex h-8 w-8 shrink-0 items-center justify-center rounded-xl">
                    <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.6]">
                        <circle cx="8" cy="5" r="3"/><path d="M2 13c0-2.5 1.5-4.5 4-5"/><path d="M10.5 9.5l3 3-3 3"/><path d="M8 14.5c0-2.8 2.2-5 5-5"/>
                    </svg>
                </span>
                <div>
                    <p class="font-display text-[0.93rem] font-semibold text-boss-ivory">{{ __('Paradise Theme') }}</p>
                    <p class="text-[0.61rem] text-boss-ivory/33">{{ __('Customize your paradise UI palette and mode') }}</p>
                </div>
            </div>
            <span x-show="isCustom" x-cloak class="pd-badge text-[0.58rem] tracking-[0.16em]">{{ __('CUSTOM') }}</span>
        </div>

        <div class="space-y-6 p-5 sm:p-6">

            {{-- Preset grid --}}
            <div>
                <p class="pd-label mb-3">{{ __('Preset Themes') }}</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <template x-for="preset in presets" :key="preset.id">
                        <button
                            type="button"
                            @click="selectPreset(preset)"
                            class="flex items-center gap-2.5 rounded-xl border p-3 text-left transition-all duration-150"
                            :class="active === preset.id
                                ? 'border-boss-gold bg-[rgba(238,180,195,0.12)]'
                                : 'border-white/[0.08] bg-white/[0.025] hover:border-white/[0.16] hover:bg-white/[0.05]'"
                        >
                            <div class="flex shrink-0 gap-1">
                                <template x-for="col in preset.colors" :key="col">
                                    <span class="h-[18px] w-[18px] rounded-md shadow-sm" :style="`background:${col};border:1px solid rgba(0,0,0,0.1)`"></span>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[0.76rem] font-medium text-boss-ivory" x-text="preset.name"></p>
                                <p class="text-[0.6rem] text-boss-ivory/35" x-text="preset.desc"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Color picker + Mode --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="pd-label mb-2">{{ __('Primary Color') }}</p>
                    <div class="flex items-center gap-3">
                        <div class="relative h-10 w-14 overflow-hidden rounded-lg border border-white/[0.12]">
                            <input
                                type="color"
                                x-model="primary"
                                @input="onColorChange()"
                                class="absolute -inset-1 h-14 w-20 cursor-pointer border-0 bg-transparent p-0 opacity-0"
                            >
                            <div class="pointer-events-none h-full w-full rounded-lg" :style="`background:${primary}`"></div>
                        </div>
                        <span class="font-mono text-[0.8rem] uppercase text-boss-ivory/55" x-text="primary"></span>
                    </div>
                </div>
                <div>
                    <p class="pd-label mb-2">{{ __('Paradise Mode') }}</p>
                    <select x-model="mode" @change="applyLive()" class="pd-input">
                        <option value="dark">{{ __('Dark Mode') }}</option>
                        <option value="light">{{ __('Light Mode') }}</option>
                    </select>
                </div>
            </div>

            {{-- Live preview bar --}}
            <div>
                <p class="pd-label mb-2">{{ __('Accent Preview') }}</p>
                <div class="h-10 rounded-lg transition-all duration-300"
                     :style="`background: linear-gradient(90deg, ${primary}, ${primaryLight})`">
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="save()" :disabled="savingTheme" class="pd-btn-primary rounded-lg px-5 py-2.5 text-[0.74rem] disabled:cursor-not-allowed disabled:opacity-60">
                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[2]"><path d="M3 8.5l3.5 3.5L13 5"/></svg>
                    <span x-text="savingTheme ? '{{ __('Saving...') }}' : (saved ? '{{ __('Saved!') }}' : '{{ __('Save Theme') }}')"></span>
                </button>
                <button type="button" @click="resetDefault()" class="pd-btn-secondary rounded-lg px-5 py-2.5 text-[0.74rem]">
                    {{ __('Reset to Default') }}
                </button>
            </div>
            <p x-show="themeError" x-cloak class="text-[0.76rem] text-red-300" x-text="themeError"></p>

        </div>
    </div>
    @endif

    <script>
    function pdThemeCustomizer() {
        return {
            presets: [
                { id: 'pink-light',  name: 'Pink Light',  desc: 'Default',  mode: 'light', primary: '#EEB4C3', primaryLight: '#F3C3CF', colors: ['#FFF0F5', '#EEB4C3', '#DFA1B4'] },
                { id: 'dark-gold',   name: 'Dark Gold',   desc: 'Classic',  mode: 'dark',  primary: '#C9A96E', primaryLight: '#E8C88A', colors: ['#09070A', '#C9A96E', '#E8C88A'] },
                { id: 'rose-night',  name: 'Rose Night',  desc: '',         mode: 'dark',  primary: '#E07080', primaryLight: '#F09090', colors: ['#09070A', '#E07080', '#F09090'] },
                { id: 'blush-dark',  name: 'Blush Dark',  desc: '',         mode: 'dark',  primary: '#DFA1B4', primaryLight: '#EEB4C3', colors: ['#09070A', '#DFA1B4', '#EEB4C3'] },
                { id: 'berry-glam',  name: 'Berry Glam',  desc: '',         mode: 'dark',  primary: '#C060A0', primaryLight: '#D880B8', colors: ['#09070A', '#C060A0', '#D880B8'] },
                { id: 'coral-light', name: 'Coral Light', desc: '',         mode: 'light', primary: '#F08070', primaryLight: '#F8A090', colors: ['#FFF5F0', '#F08070', '#F8A090'] },
            ],
            active:       'pink-light',
            mode:         'light',
            primary:      '#EEB4C3',
            primaryLight: '#F3C3CF',
            saved:        false,
            savingTheme:  false,
            themeError:   '',

            init() {
                // Load the current global theme from the server (DB-backed)
                var s = @json($siteTheme);
                if (s) {
                    if (s.preset)       this.active       = s.preset;
                    if (s.mode)         this.mode         = s.mode;
                    if (s.primary)      this.primary      = s.primary;
                    if (s.primaryLight) this.primaryLight = s.primaryLight;
                }
            },

            get isCustom() {
                var p = this.presets.find(p => p.id === this.active);
                return !p || p.primary !== this.primary || p.mode !== this.mode;
            },

            selectPreset(preset) {
                this.active       = preset.id;
                this.mode         = preset.mode;
                this.primary      = preset.primary;
                this.primaryLight = preset.primaryLight;
                this.applyLive();
            },

            onColorChange() {
                this.active       = 'custom';
                this.primaryLight = this.lighten(this.primary, 22);
                this.applyLive();
            },

            lighten(hex, amt) {
                var n = parseInt(hex.replace('#',''), 16);
                var r = Math.min(255, (n >> 16) + amt);
                var g = Math.min(255, ((n >> 8) & 0xFF) + amt);
                var b = Math.min(255, (n & 0xFF) + amt);
                return '#' + ((r<<16)|(g<<8)|b).toString(16).padStart(6,'0');
            },

            applyLive() {
                window.pdApplyTheme({
                    preset:       this.active,
                    mode:         this.mode,
                    primary:      this.primary,
                    primaryLight: this.primaryLight,
                });
            },

            async save() {
                this.applyLive();
                this.saved = false;
                this.themeError = '';
                this.savingTheme = true;

                try {
                    const response = await fetch('{{ route('admin.settings.theme') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({
                            preset:       this.active,
                            mode:         this.mode,
                            primary:      this.primary,
                            primaryLight: this.primaryLight,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('{{ __('The theme could not be saved. Please refresh and try again.') }}');
                    }

                    this.saved = true;
                    setTimeout(() => this.saved = false, 2200);
                } catch (error) {
                    this.themeError = error.message || '{{ __('The theme could not be saved. Please refresh and try again.') }}';
                } finally {
                    this.savingTheme = false;
                }
            },

            resetDefault() {
                this.active       = 'pink-light';
                this.mode         = 'light';
                this.primary      = '#EEB4C3';
                this.primaryLight = '#F3C3CF';
                this.applyLive();
            },
        };
    }
    </script>

    @unless($isAdmin)
        {{-- ── Danger zone ──────────────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-red-500/13 pd-inner-card-bg">
            <div class="p-5 sm:p-6">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    @endunless

</div>
</x-dynamic-component>
