@php($hero = 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&q=85&w=1920')
<x-layouts.marketing :title="__('Work From Paradise')">
    <section class="relative flex min-h-[64vh] items-center justify-center overflow-hidden pt-24">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $hero }}');"></div>
        <div class="absolute inset-0 bg-black/45"></div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center text-white">
            <p class="mb-4 text-[0.7rem] uppercase tracking-[0.4em] text-boss-gold">{{ __('Freedom & Travel') }}</p>
            <h1 class="font-display text-[clamp(2.7rem,6vw,4.5rem)] leading-tight">{{ __('Work From Paradise') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-[1rem] leading-relaxed text-white/85">{{ __('Tropical destinations, cafés, villas, beach clubs, and luxury apartments become part of the bigger vision: remote income with structure behind it.') }}</p>
        </div>
    </section>

    <section class="bg-boss-cream py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ([
                    [__('Portable workflow'), __('Equipment and connectivity guidance for members who want to build while travelling.')],
                    [__('Lifestyle planning'), __('Schedules, timezones, and content preparation that keep the work realistic on the move.')],
                    [__('Paradise branding'), __('Visual direction that connects freedom, feminine success, and remote online opportunity.')],
                ] as $item)
                    <div class="bg-white p-7 shadow-luxe">
                        <h2 class="font-display text-[1.35rem] text-boss-dark">{{ $item[0] }}</h2>
                        <p class="mt-3 text-[0.88rem] leading-relaxed text-boss-dark/60">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('home') }}#apply" class="mt-10 inline-block bg-boss-dark px-10 py-3 text-[0.7rem] uppercase tracking-[0.15em] text-white transition-colors hover:bg-boss-gold">{{ __('Join us') }}</a>
        </div>
    </section>
</x-layouts.marketing>
