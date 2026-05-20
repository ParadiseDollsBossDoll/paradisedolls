<form
    method="POST"
    action="{{ route('profile.update') }}"
    enctype="multipart/form-data"
    class="space-y-4"
    x-data="{
        saving: false,
        saved: {{ session('status') === 'profile-updated' ? 'true' : 'false' }},
    }"
    @submit="saving = true"
>
    @csrf
    @method('PATCH')

    {{-- Name --}}
    <div>
        <label for="name" class="block text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/50">
            {{ __('Display Name') }}
        </label>
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-2 block w-full"
            :value="old('name', $user->name)"
            required
            autofocus
            autocomplete="name"
        />
        <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
    </div>

    {{-- Email — read-only display, submitted as hidden field --}}
    <input type="hidden" name="email" value="{{ $user->email }}">
    <div>
        <label class="block text-[0.68rem] uppercase tracking-[0.14em] text-boss-ivory/50">
            {{ __('Email Address') }}
        </label>
        <div class="mt-2 flex items-center gap-2.5 rounded-lg border border-white/[0.06] bg-white/[0.02] px-3.5 py-2.5">
            <span class="flex-1 truncate text-sm text-boss-ivory/50">{{ $user->email }}</span>
            <span class="shrink-0 rounded border border-white/[0.07] bg-white/[0.03] px-2 py-0.5 text-[0.58rem] uppercase tracking-[0.12em] text-boss-ivory/25">{{ __('fixed') }}</span>
        </div>
        <p class="mt-1.5 text-[0.61rem] text-boss-ivory/25">{{ __('Contact support to change your email address.') }}</p>
    </div>

    {{-- Save row --}}
    <div class="flex items-center gap-3 pt-1">
        <button
            type="submit"
            :disabled="saving"
            class="pd-profile-save-btn tracking-wide disabled:opacity-50"
        >
            <svg x-show="saving" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12"/>
            </svg>
            <span x-text="saving ? '{{ __('Saving…') }}' : '{{ __('Save Changes') }}'"></span>
        </button>

        <span
            x-show="saved && !saving"
            x-transition
            x-init="saved && setTimeout(() => saved = false, 3000)"
            class="flex items-center gap-1.5 text-[0.72rem] text-emerald-300"
        >
            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[2.2]">
                <path d="M3 8.5l3.5 3.5L13 5"/>
            </svg>
            {{ __('Saved') }}
        </span>
    </div>
</form>
