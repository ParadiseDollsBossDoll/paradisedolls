<x-guest-layout>
    <div class="w-full max-w-xl space-y-6 text-boss-ivory">
        <div class="text-center">
            <p class="pd-kicker">{{ __('Chatter Team') }}</p>
            <h1 class="pd-heading mt-2 text-3xl">{{ __('Request workspace access') }}</h1>
            <p class="mt-3 text-sm text-boss-ivory/55">{{ __('Submit your details for admin approval. This request does not create an active account.') }}</p>
        </div>
        @if(session('status'))<div class="rounded-md border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('chatter.apply.store') }}" class="space-y-5 rounded-md border border-white/[0.08] bg-white/[0.035] p-6">@csrf
            <div><x-input-label for="name" :value="__('Full name')"/><x-text-input id="name" name="name" class="mt-2 block w-full" :value="old('name')" required/><x-input-error :messages="$errors->get('name')" class="mt-2"/></div>
            <div><x-input-label for="email" :value="__('Email address')"/><x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email')" required/><x-input-error :messages="$errors->get('email')" class="mt-2"/></div>
            <div><x-input-label for="timezone" :value="__('Your timezone')"/><select id="timezone" name="timezone" class="pd-select mt-2 w-full" required>@foreach($timezones as $value=>$label)<option value="{{ $value }}" @selected(old('timezone')===$value)>{{ $label }}</option>@endforeach</select><x-input-error :messages="$errors->get('timezone')" class="mt-2"/></div>
            <button type="submit" class="pd-btn-primary w-full">{{ __('Send Access Request') }}</button>
        </form>
        <p class="text-center text-sm text-boss-ivory/40"><a href="{{ route('login') }}" class="text-boss-gold hover:underline">{{ __('Already invited? Log in') }}</a></p>
    </div>
</x-guest-layout>
