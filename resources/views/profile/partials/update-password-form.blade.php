<section class="h-full">
    <header class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-boss-gold/15 bg-boss-gold/[0.08] text-boss-gold">
            <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.7]"><rect x="3" y="7" width="10" height="7" rx="1.6"></rect><path d="M5.2 7V5.3a2.8 2.8 0 0 1 5.6 0V7"></path></svg>
        </span>
        <div>
            <h2 class="pd-heading text-[1.28rem] text-boss-ivory">
                {{ __('Update Password') }}
            </h2>

            <p class="mt-2 text-sm leading-6 text-boss-ivory/42">
                {{ __('Use a long, unique password to keep your account secure.') }}
            </p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-7 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="rounded-xl border border-white/[0.06] bg-black/15 px-3 py-2 text-[0.72rem] leading-5 text-boss-ivory/38">
            {{ __('A stronger password usually includes 12+ characters with a mix of words, numbers, and symbols.') }}
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-1">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="rounded-full border border-emerald-400/15 bg-emerald-400/10 px-3 py-1 text-sm text-emerald-200"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
