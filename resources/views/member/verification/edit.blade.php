<x-member-layout>
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <p class="pd-kicker">{{ __('Onboarding') }}</p>
            <h1 class="pd-heading pd-text-gradient mt-2 text-[clamp(2rem,4vw,2.6rem)]">{{ __('Verification') }}</h1>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-400/20 bg-green-400/10 p-4 text-sm text-green-200">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="pd-panel-strong p-5 md:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-[0.66rem] uppercase tracking-[0.18em] text-boss-ivory/35">{{ __('Current status') }}</p>
                    <h2 class="pd-heading mt-1 text-[1.35rem] text-boss-ivory">{{ $profile->verificationStatusLabel() }}</h2>
                </div>
                <div class="w-full md:w-52">
                    <div class="flex items-center justify-between text-[0.66rem] uppercase tracking-[0.12em] text-boss-ivory/30">
                        <span>{{ __('Onboarding') }}</span>
                        <span class="text-boss-gold">{{ $profile->onboardingPercent() }}%</span>
                    </div>
                    <div class="pd-progress-track mt-2">
                        <div class="pd-progress-bar" style="width: {{ $profile->onboardingPercent() }}%"></div>
                    </div>
                </div>
            </div>

            @if (! $profile->hasInformationForm())
                <div class="mt-5 rounded-xl border border-amber-300/20 bg-amber-300/10 p-4 text-sm text-amber-100">
                    {{ __('Submit the Model Information Form before uploading verification documents.') }}
                    <a href="{{ route('member.onboarding.edit') }}" class="ml-2 text-boss-gold hover:text-boss-gold-light">{{ __('Open form') }}</a>
                </div>
            @endif

            @if ($profile->verification_notes && $profile->verification_status === \App\Models\ModelProfile::VERIFICATION_REJECTED)
                <div class="mt-5 rounded-xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-100">
                    <p class="font-medium">{{ __('Resubmission instructions') }}</p>
                    <p class="mt-1 whitespace-pre-line text-red-100/70">{{ $profile->verification_notes }}</p>
                </div>
            @endif

            @if (filled($profile->verification_request_instructions))
                <div class="mt-5 rounded-xl border border-boss-gold/25 bg-boss-gold/10 p-4 text-sm text-boss-ivory">
                    <p class="font-medium text-boss-gold">{{ __('Instructions from Kayla') }}</p>
                    <p class="mt-1 whitespace-pre-line text-boss-ivory/70">{{ $profile->verification_request_instructions }}</p>
                </div>
            @endif
        </section>

        @if ($profile->hasInformationForm())
            <form method="POST" action="{{ route('member.verification.store') }}" enctype="multipart/form-data" class="pd-panel p-5 md:p-6">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="id_document" class="pd-label">{{ __('Valid ID') }}</label>
                        <input
                            id="id_document"
                            type="file"
                            name="id_document"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="mt-2 block w-full cursor-pointer rounded-xl border border-boss-rose/25 bg-white/80 px-3 py-2 text-[0.82rem] text-boss-ink/70 shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-boss-rose/20 file:px-4 file:py-2 file:text-[0.72rem] file:font-semibold file:text-boss-rose file:transition-colors hover:file:bg-boss-rose/30 focus:border-boss-rose/45 focus:outline-none focus:ring-2 focus:ring-boss-rose/15"
                            @if (! $profile->id_document_path) required @endif
                        >
                        @if ($profile->id_document_path)
                            <p class="mt-2 text-[0.72rem] text-boss-ivory/28">{{ __('Existing file on record. Leave blank to keep it.') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="selfie_with_id" class="pd-label">{{ __('Selfie holding ID') }}</label>
                        <input
                            id="selfie_with_id"
                            type="file"
                            name="selfie_with_id"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="mt-2 block w-full cursor-pointer rounded-xl border border-boss-rose/25 bg-white/80 px-3 py-2 text-[0.82rem] text-boss-ink/70 shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-boss-rose/20 file:px-4 file:py-2 file:text-[0.72rem] file:font-semibold file:text-boss-rose file:transition-colors hover:file:bg-boss-rose/30 focus:border-boss-rose/45 focus:outline-none focus:ring-2 focus:ring-boss-rose/15"
                            @if (! $profile->selfie_with_id_path) required @endif
                        >
                        @if ($profile->selfie_with_id_path)
                            <p class="mt-2 text-[0.72rem] text-boss-ivory/28">{{ __('Existing file on record. Leave blank to keep it.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('member.dashboard') }}" class="pd-btn-secondary">{{ __('Back to dashboard') }}</a>
                    <button type="submit" class="pd-btn-primary">{{ __('Submit Verification') }}</button>
                </div>
            </form>
        @endif
    </div>
</x-member-layout>
