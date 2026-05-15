<x-dynamic-component :component="$user->isAdmin() ? 'admin-layout' : 'member-layout'">
@php
    $profilePhotoUrl = $user->profilePhotoUrl();
    $isAdmin         = $user->isAdmin();
    $roleLabel       = $isAdmin ? __('Administrator') : __('Paradise Dolls Member');
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
        <span class="inline-flex items-center gap-2 rounded-full border border-boss-gold/20 bg-boss-gold/[0.07] px-3.5 py-1.5 text-[0.63rem] uppercase tracking-[0.15em] text-boss-gold/75">
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
            saving: false,
            setPhoto(event) {
                const file = event.target.files?.[0];
                if (!file) return;
                if (this.previewUrl && this.selected) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = URL.createObjectURL(file);
                this.fileName   = file.name;
                this.selected   = true;
            },
            resetSelection() {
                if (this.previewUrl && this.selected) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = @js($profilePhotoUrl);
                this.fileName   = '';
                this.selected   = false;
                this.$refs.photoInput.value = '';
            },
        }"
        class="relative overflow-hidden rounded-2xl border border-white/[0.07]"
        style="background: linear-gradient(105deg, rgba(201,169,110,0.12) 0%, rgba(196,104,122,0.05) 45%, transparent 75%), #0E0B13;"
    >
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-boss-gold/35 to-transparent"></div>

        <form
            method="POST"
            action="{{ route('profile.update') }}"
            enctype="multipart/form-data"
            @submit="saving = true"
            class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:gap-6 sm:p-6 lg:p-7"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="name"  value="{{ old('name',  $user->name) }}">
            <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
            <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp"
                   class="sr-only" x-ref="photoInput" @change="setPhoto($event)">

            {{-- Avatar --}}
            <button type="button" @click="$refs.photoInput.click()"
                class="group relative mx-auto h-[84px] w-[84px] shrink-0 cursor-pointer rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-boss-gold sm:mx-0"
                :title="previewUrl ? '{{ __('Change photo') }}' : '{{ __('Upload photo') }}'">
                <div class="absolute inset-0 rounded-2xl p-[2px]"
                     :class="selected ? 'bg-gradient-to-br from-boss-gold to-[#C4687A]' : 'bg-gradient-to-br from-boss-gold/40 to-boss-rose/20'">
                    <div class="flex h-full w-full items-center justify-center rounded-[14px]"
                         style="background: radial-gradient(circle at 30% 25%, rgba(201,169,110,0.25), rgba(12,10,16,0.95) 70%);">
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
                <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-[#0E0B13] bg-emerald-400"></span>
            </button>

            {{-- Info --}}
            <div class="min-w-0 flex-1 text-center sm:text-left">
                <h2 class="truncate font-display text-xl font-semibold text-boss-ivory sm:text-2xl">{{ $user->name }}</h2>
                <p class="mt-0.5 truncate text-sm text-boss-ivory/40">{{ $user->email }}</p>
                <div class="mt-2.5 inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[0.62rem] uppercase tracking-[0.13em]"
                     style="background:rgba(201,169,110,0.07);border-color:rgba(201,169,110,0.17);color:rgba(201,169,110,0.72);">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    {{ $roleLabel }}
                </div>
            </div>

            {{-- Photo actions --}}
            <div class="flex flex-wrap items-center justify-center gap-2 sm:flex-col sm:flex-nowrap sm:items-end sm:justify-start">
                <button type="button" @click="$refs.photoInput.click()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.09] bg-white/[0.04] px-3.5 py-2 text-[0.72rem] text-boss-ivory/55 transition-all hover:border-boss-gold/28 hover:bg-boss-gold/[0.08] hover:text-boss-gold">
                    <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.5]">
                        <path d="M8 2v8M5 5l3-3 3 3"/><path d="M3 11v1a2 2 0 002 2h6a2 2 0 002-2v-1"/>
                    </svg>
                    <span x-text="previewUrl ? '{{ __('Change Photo') }}' : '{{ __('Upload Photo') }}'"></span>
                </button>
                <button type="submit" x-show="selected" x-cloak :disabled="saving"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-boss-gold/33 bg-boss-gold/[0.11] px-3.5 py-2 text-[0.72rem] text-boss-gold transition-all hover:bg-boss-gold/18 disabled:opacity-50">
                    <svg x-show="saving" class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12"/>
                    </svg>
                    <span x-text="saving ? '{{ __('Saving…') }}' : '{{ __('Save Photo') }}'"></span>
                </button>
                <button type="button" x-show="selected" x-cloak @click="resetSelection()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.07] bg-white/[0.03] px-3.5 py-2 text-[0.72rem] text-boss-ivory/38 transition-all hover:text-boss-ivory">
                    {{ __('Cancel') }}
                </button>
                @if ($profilePhotoUrl)
                    <button type="submit" name="remove_profile_photo" value="1"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-400/13 bg-red-400/[0.04] px-3.5 py-2 text-[0.72rem] text-red-300/45 transition-all hover:border-red-400/22 hover:bg-red-400/[0.07] hover:text-red-200">
                        {{ __('Remove Photo') }}
                    </button>
                @endif
                <p class="w-full text-center text-[0.61rem] text-boss-ivory/22 sm:text-right">
                    <span x-show="!selected">{{ __('JPG, PNG, WEBP · 4 MB max') }}</span>
                    <span x-show="selected" x-cloak x-text="fileName" class="text-boss-gold/45"></span>
                </p>
                <x-input-error :messages="$errors->get('profile_photo')" />
            </div>
        </form>
    </section>

    {{-- ── Settings grid: Profile Details + Password & Security ────── --}}
    <div class="grid gap-5 lg:grid-cols-2">

        {{-- Profile Details --}}
        <div class="overflow-hidden rounded-2xl border border-white/[0.07]" style="background:#0E0B13;">
            <div class="flex items-center gap-3 border-b border-white/[0.05] px-5 py-4 sm:px-6">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl"
                      style="background:rgba(201,169,110,0.09);border:1px solid rgba(201,169,110,0.16);color:#C9A96E;">
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

        {{-- Password & Security — collapsible, no static header here --}}
        <div class="overflow-hidden rounded-2xl border border-white/[0.07]" style="background:#0E0B13;">
            @include('profile.partials.update-password-form')
        </div>

    </div>

    {{-- ── Danger zone ──────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-2xl border {{ $isAdmin ? 'border-white/[0.05]' : 'border-red-500/13' }}"
         style="background:#0E0B13;">
        <div class="p-5 sm:p-6">
            @include('profile.partials.delete-user-form')
        </div>
    </div>

</div>
</x-dynamic-component>
