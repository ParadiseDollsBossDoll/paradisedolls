<x-layouts.marketing :title="marketing_content('doll_after_dark.hero.title')" :description="marketing_content('doll_after_dark.hero.body')" :image="marketing_image('doll_after_dark.hero.image')">
    <div class="dad">
        <div class="dad-hero" id="top">
            <h1 class="sr-only">{{ marketing_content('doll_after_dark.hero.title') }}</h1>
            <img src="{{ marketing_image('doll_after_dark.hero.image') }}" width="1671" height="941" fetchpriority="high" alt="{{ marketing_content('doll_after_dark.hero.alt') }}">
            <p>{{ marketing_content('doll_after_dark.hero.body') }}</p>
            <div class="dad-buttons">
                <a class="dad-btn" href="#join">{{ marketing_content('doll_after_dark.hero.course_label') }}</a>
                <a class="dad-btn dad-btn-alt" href="#agency">{{ marketing_content('doll_after_dark.hero.agency_label') }}</a>
            </div>
        </div>

        <section id="about">
            <div class="dad-wrap">
                <div class="dad-intro">
                    <span class="dad-eyebrow">{{ marketing_content('doll_after_dark.about.eyebrow') }}</span>
                    <h2>{{ marketing_content('doll_after_dark.about.title') }}</h2>
                    <p>{{ marketing_content('doll_after_dark.about.body') }}</p>
                </div>
                <div class="dad-duo">
                    <article class="dad-persona">
                        <img src="{{ marketing_image('doll_after_dark.angel.image') }}" width="1824" height="2288" loading="lazy" alt="{{ marketing_content('doll_after_dark.angel.alt') }}">
                        <div class="dad-copy">
                            <h3>{{ marketing_content('doll_after_dark.angel.title') }}</h3>
                            <p>{{ marketing_content('doll_after_dark.angel.body') }}</p>
                        </div>
                    </article>
                    <article class="dad-persona dad-persona-dark">
                        <img src="{{ marketing_image('doll_after_dark.devil.image') }}" width="1824" height="2288" loading="lazy" alt="{{ marketing_content('doll_after_dark.devil.alt') }}">
                        <div class="dad-copy">
                            <h3>{{ marketing_content('doll_after_dark.devil.title') }}</h3>
                            <p>{{ marketing_content('doll_after_dark.devil.body') }}</p>
                        </div>
                    </article>
                </div>
                <p class="dad-note">{{ marketing_content('doll_after_dark.about.note') }}</p>
            </div>
        </section>

        <section class="dad-tinted">
            <div class="dad-wrap">
                <div class="dad-intro">
                    <span class="dad-eyebrow">{{ marketing_content('doll_after_dark.styles.eyebrow') }}</span>
                    <h2>{{ marketing_content('doll_after_dark.styles.title') }}</h2>
                    <p>{{ marketing_content('doll_after_dark.styles.body') }}</p>
                </div>
                <ul class="dad-styles" aria-label="Creator styles">
                    @foreach (marketing_items('doll_after_dark.styles.items') as $style)
                        <li>{{ $style }}</li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section>
            <div class="dad-wrap dad-editorial">
                <img src="{{ marketing_image('doll_after_dark.training.image') }}" width="1824" height="2288" loading="lazy" alt="{{ marketing_content('doll_after_dark.training.alt') }}">
                <div>
                    <span class="dad-eyebrow">{{ marketing_content('doll_after_dark.training.eyebrow') }}</span>
                    <h2>{{ marketing_content('doll_after_dark.training.title') }}</h2>
                    <p>{{ marketing_content('doll_after_dark.training.body') }}</p>
                    <ul class="dad-points">
                        @foreach (marketing_items('doll_after_dark.training.items') as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <section class="dad-join" id="join">
            <div class="dad-wrap">
                <div class="dad-intro">
                    <span class="dad-eyebrow">{{ marketing_content('doll_after_dark.join.eyebrow') }}</span>
                    <h2>{{ marketing_content('doll_after_dark.join.title') }}</h2>
                    <p>{{ marketing_content('doll_after_dark.join.body') }}</p>
                </div>
                <div class="dad-routes">
                    <article class="dad-route">
                        <span class="dad-eyebrow">{{ marketing_content('doll_after_dark.course.eyebrow') }}</span>
                        <h3>{{ marketing_content('doll_after_dark.course.title') }}</h3>
                        <p>{{ marketing_content('doll_after_dark.course.body') }}</p>
                        <ul>
                            @foreach (marketing_items('doll_after_dark.course.items') as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                        <button class="dad-btn" type="button" disabled>{{ marketing_content('doll_after_dark.course.button_label') }}</button>
                    </article>
                    <article class="dad-route dad-featured" id="agency">
                        <span class="dad-eyebrow">{{ marketing_content('doll_after_dark.agency.eyebrow') }}</span>
                        <h3>{{ marketing_content('doll_after_dark.agency.title') }}</h3>
                        <p>{{ marketing_content('doll_after_dark.agency.body') }}</p>
                        <ul>
                            @foreach (marketing_items('doll_after_dark.agency.items') as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                        <a class="dad-btn" href="{{ route('apply') }}">{{ marketing_content('doll_after_dark.agency.button_label') }}</a>
                    </article>
                </div>
            </div>
        </section>

        <section>
            <div class="dad-faq">
                <div class="dad-intro">
                    <span class="dad-eyebrow">{{ marketing_content('doll_after_dark.faq.eyebrow') }}</span>
                    <h2>{{ marketing_content('doll_after_dark.faq.title') }}</h2>
                </div>
                @foreach (marketing_items('doll_after_dark.faq.items') as $faq)
                    <details><summary>{{ $faq['question'] ?? '' }}</summary><p>{{ $faq['answer'] ?? '' }}</p></details>
                @endforeach
            </div>
        </section>

        <section class="dad-last">
            <span class="dad-eyebrow">{{ marketing_content('doll_after_dark.closing.eyebrow') }}</span>
            <h2>{{ marketing_content('doll_after_dark.closing.title') }}</h2>
            <p>{{ marketing_content('doll_after_dark.closing.body') }}</p>
            <a href="#join" class="dad-btn">{{ marketing_content('doll_after_dark.closing.button_label') }}</a>
        </section>
        <div class="dad-disclaimer dad-wrap">
            <p>{{ marketing_content('doll_after_dark.disclaimer.age') }}</p>
            <p>{{ marketing_content('doll_after_dark.disclaimer.terms') }}</p>
        </div>
    </div>
</x-layouts.marketing>
