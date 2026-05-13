<section class="h-full">
    <header class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-boss-gold/15 bg-boss-gold/[0.08] text-boss-gold">
            <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.7]"><circle cx="8" cy="5" r="3"></circle><path d="M2.5 14c0-3.2 2.5-5.5 5.5-5.5s5.5 2.3 5.5 5.5"></path></svg>
        </span>
        <div>
            <h2 class="pd-heading text-[1.28rem] text-boss-ivory">
                {{ __('Profile Information') }}
            </h2>

            <p class="mt-2 text-sm leading-6 text-boss-ivory/42">
                {{ __("Update your public account details and contact email.") }}
            </p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-7 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-boss-ivory/50">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-boss-gold hover:text-boss-gold-light rounded-md">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-300">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-1">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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
