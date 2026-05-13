<section class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
    <header class="max-w-3xl">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-red-300/20 bg-red-500/10 text-red-200">
                <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.7]"><path d="M2 4h12"></path><path d="M6 4V2.5h4V4"></path><path d="M4 4l.6 9.5A1.5 1.5 0 0 0 6.1 15h3.8a1.5 1.5 0 0 0 1.5-1.5L12 4"></path></svg>
            </span>
            <div>
                <p class="text-[0.66rem] uppercase tracking-[0.18em] text-red-200/70">{{ __('Danger Zone') }}</p>
                <h2 class="pd-heading mt-1 text-[1.28rem] text-red-100">
                    {{ __('Delete Account') }}
                </h2>

                <p class="mt-2 text-sm leading-6 text-boss-ivory/42">
                    {{ __('Once your account is deleted, all resources and data attached to it will be permanently removed. Download anything you want to keep first.') }}
                </p>
            </div>
        </div>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="w-fit shrink-0"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="pd-heading text-[1.25rem] text-boss-ivory">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-boss-ivory/40">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
