<x-dynamic-component :component="$user->isAdmin() ? 'admin-layout' : 'member-layout'">
    @php
        $profilePhotoUrl = $user->profilePhotoUrl();
        $accountLabel = $user->isAdmin() ? __('Administrator') : __('Paradise Dolls Member');
    @endphp

    <div class="mx-auto max-w-6xl space-y-7 text-boss-ivory">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="pd-kicker">{{ __('Account') }}</p>
                <h1 class="pd-heading mt-2 text-[clamp(2.15rem,4vw,3.2rem)]">{{ __('Profile') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-boss-ivory/45">
                    {{ __('Manage your account information, profile photo, and security settings.') }}
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-boss-gold/15 bg-boss-gold/[0.06] px-3.5 py-2 text-[0.68rem] uppercase tracking-[0.14em] text-boss-gold">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                {{ __('Account active') }}
            </div>
        </header>

        <section
            x-data="{
                previewUrl: @js($profilePhotoUrl),
                selected: false,
                fileName: '',
                setPhoto(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    if (this.previewUrl && this.selected) URL.revokeObjectURL(this.previewUrl);
                    this.previewUrl = URL.createObjectURL(file);
                    this.fileName = file.name;
                    this.selected = true;
                },
                resetSelection() {
                    if (this.previewUrl && this.selected) URL.revokeObjectURL(this.previewUrl);
                    this.previewUrl = @js($profilePhotoUrl);
                    this.fileName = '';
                    this.selected = false;
                    this.$refs.profilePhotoInput.value = '';
                },
            }"
            class="overflow-hidden rounded-2xl border border-white/[0.07] bg-[linear-gradient(135deg,rgba(201,169,110,0.09),rgba(255,255,255,0.025)_42%,rgba(196,104,122,0.055))] shadow-[0_24px_70px_rgba(0,0,0,0.24)]"
        >
            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid gap-6 p-5 sm:p-6 lg:grid-cols-[auto,1fr] lg:items-center lg:p-7">
                @csrf
                @method('patch')
                <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
                <input type="hidden" name="email" value="{{ old('email', $user->email) }}">

                <div class="relative mx-auto h-28 w-28 shrink-0 lg:mx-0">
                    <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-2xl border border-boss-gold/20 bg-[radial-gradient(circle_at_top,rgba(201,169,110,0.28),rgba(19,15,18,0.92)_68%)] font-display text-3xl font-semibold text-boss-gold-light shadow-[0_18px_38px_rgba(0,0,0,0.28)]">
                        <span>{{ $user->initials() }}</span>
                        <img x-show="previewUrl" x-cloak :src="previewUrl" alt="{{ __('Profile photo preview') }}" class="absolute inset-0 h-28 w-28 rounded-2xl object-cover" x-on:error="previewUrl = null">
                    </div>
                    <span class="absolute -bottom-1 -right-1 rounded-full border-4 border-[#111015] bg-emerald-400 p-1.5"></span>
                </div>

                <div class="min-w-0 text-center lg:text-left">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-gold/70">{{ __('Profile Overview') }}</p>
                            <h2 class="mt-2 truncate font-display text-2xl font-semibold text-boss-ivory">{{ $user->name }}</h2>
                            <p class="mt-1 truncate text-sm text-boss-ivory/45">{{ $user->email }}</p>
                            <p class="mt-3 inline-flex rounded-full border border-white/8 bg-black/20 px-3 py-1 text-[0.68rem] text-boss-ivory/48">{{ $accountLabel }}</p>
                        </div>

                        <div class="flex flex-col items-center gap-2 lg:items-end">
                            <input x-ref="profilePhotoInput" id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="setPhoto($event)">
                            <div class="flex flex-wrap justify-center gap-2 lg:justify-end">
                                <button type="button" class="pd-btn-secondary px-3 py-2 text-[0.66rem]" @click="$refs.profilePhotoInput.click()">
                                    <span x-text="previewUrl ? '{{ __('Change Photo') }}' : '{{ __('Upload Photo') }}'"></span>
                                </button>
                                <button type="submit" x-show="selected" x-cloak class="pd-btn-primary px-3 py-2 text-[0.66rem]">
                                    {{ __('Save Photo') }}
                                </button>
                                <button type="button" x-show="selected" x-cloak class="pd-btn-secondary px-3 py-2 text-[0.66rem]" @click="resetSelection()">
                                    {{ __('Cancel') }}
                                </button>
                                @if ($profilePhotoUrl)
                                    <button type="submit" name="remove_profile_photo" value="1" class="pd-btn-secondary px-3 py-2 text-[0.66rem] text-red-200 hover:border-red-300/30 hover:bg-red-500/10 hover:text-red-100">
                                        {{ __('Remove Photo') }}
                                    </button>
                                @endif
                            </div>
                            <p class="max-w-xs text-center text-[0.68rem] leading-5 text-boss-ivory/35 lg:text-right">
                                <span x-show="!selected">{{ __('JPG, PNG or WEBP. Max 4MB.') }}</span>
                                <span x-show="selected" x-cloak x-text="fileName"></span>
                            </p>
                            <x-input-error class="text-right" :messages="$errors->get('profile_photo')" />
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="pd-panel p-5 sm:p-7">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="pd-panel p-5 sm:p-7">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-2xl border border-red-400/15 bg-[linear-gradient(135deg,rgba(239,68,68,0.08),rgba(19,15,18,0.92))] p-5 shadow-[0_20px_60px_rgba(0,0,0,0.18)] sm:p-7">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-dynamic-component>
