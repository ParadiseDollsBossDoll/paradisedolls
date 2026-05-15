@if (auth()->user()->isAdmin())
    {{-- Admins cannot self-delete — prevents accidental platform lockout --}}
    <div class="flex items-start gap-3">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-white/[0.07] bg-white/[0.03] text-boss-ivory/30">
            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.6]">
                <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3.5M8 10.5v.5"/>
            </svg>
        </span>
        <div>
            <p class="font-display text-[0.85rem] font-semibold text-boss-ivory/55">{{ __('Account Deletion') }}</p>
            <p class="mt-0.5 text-[0.7rem] leading-5 text-boss-ivory/30">
                {{ __('Administrator accounts cannot be deleted here. Contact your platform owner to remove this account.') }}
            </p>
        </div>
    </div>

@else

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-red-400/20 bg-red-500/[0.08] text-red-300/70">
                <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[1.6]">
                    <path d="M2 4h12M6 4V2.5h4V4M4 4l.6 9.5A1.5 1.5 0 006.1 15h3.8a1.5 1.5 0 001.5-1.5L12 4"/>
                </svg>
            </span>
            <div>
                <p class="font-display text-[0.85rem] font-semibold text-red-200/75">{{ __('Delete Account') }}</p>
                <p class="mt-0.5 text-[0.7rem] leading-5 text-boss-ivory/30">
                    {{ __('Permanently removes your account and all data. This cannot be undone.') }}
                </p>
            </div>
        </div>

        <button
            type="button"
            x-data=""
            @click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="shrink-0 rounded-full border border-red-400/20 bg-red-500/[0.06] px-4 py-2 text-[0.72rem] text-red-300/65 transition-all hover:border-red-400/35 hover:bg-red-500/12 hover:text-red-200"
        >
            {{ __('Delete my account') }}
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="POST" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('DELETE')

            <h2 class="font-display text-[1.15rem] font-semibold text-boss-ivory">
                {{ __('Delete your account?') }}
            </h2>
            <p class="mt-2 text-[0.8rem] leading-6 text-boss-ivory/40">
                {{ __('This is permanent. All your progress, profile, and data will be gone forever. Enter your password to confirm.') }}
            </p>

            <div class="mt-5">
                <x-input-label for="delete_password" :value="__('Password')" class="sr-only" />
                <x-text-input
                    id="delete_password"
                    name="password"
                    type="password"
                    class="block w-full"
                    placeholder="{{ __('Your current password') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    @click="$dispatch('close')"
                    class="rounded-full border border-white/[0.08] bg-white/[0.04] px-4 py-2 text-[0.75rem] text-boss-ivory/55 transition-all hover:text-boss-ivory"
                >
                    {{ __('Cancel') }}
                </button>
                <button
                    type="submit"
                    class="rounded-full border border-red-400/25 bg-red-500/12 px-4 py-2 text-[0.75rem] font-medium text-red-200/80 transition-all hover:bg-red-500/22 hover:text-red-100"
                >
                    {{ __('Yes, delete my account') }}
                </button>
            </div>
        </form>
    </x-modal>

@endif
