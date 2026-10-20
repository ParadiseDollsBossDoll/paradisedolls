<x-layouts.marketing title="Doll After Dark" description="Find your creator identity and learn how to build an online brand around your personality, style and boundaries." :image="asset('images/doll-after-dark/hero.png')">
    <div class="dad">
        <div class="dad-hero" id="top">
            <h1 class="sr-only">Doll After Dark</h1>
            <img src="{{ asset('images/doll-after-dark/hero.png') }}" width="1671" height="941" fetchpriority="high" alt="Boss Doll Creators presents Doll After Dark: pink Angel and black Devil holding phones. Heavenly feet. Devilish heels.">
            <p>Find your creator identity and learn how to build an online brand around your personality, style and boundaries.</p>
            <div class="dad-buttons">
                <a class="dad-btn" href="#join">EXPLORE THE COURSE</a>
                <a class="dad-btn dad-btn-alt" href="#agency">EXPLORE AGENCY SUPPORT</a>
            </div>
        </div>

        <section id="about">
            <div class="dad-wrap">
                <div class="dad-intro">
                    <span class="dad-eyebrow">Two sides. Your choice.</span>
                    <h2>Which Doll are you?</h2>
                    <p>Soft, spoiled and a little bratty. Bold, confident and commanding. Or a little of both.</p>
                </div>
                <div class="dad-duo">
                    <article class="dad-persona">
                        <img src="{{ asset('images/doll-after-dark/angel.jpg') }}" width="1824" height="2288" loading="lazy" alt="Angel styling with white feathered wings and an embellished ivory outfit">
                        <div class="dad-copy">
                            <h3>Angel energy</h3>
                            <p>Pretty details, playful personality and pampered princess energy. From bare feet to cosy collections, explore a softer style with standards of your own.</p>
                        </div>
                    </article>
                    <article class="dad-persona dad-persona-dark">
                        <img src="{{ asset('images/doll-after-dark/devil.jpg') }}" width="1824" height="2288" loading="lazy" alt="Darker fashion styling with a black corset">
                        <div class="dad-copy">
                            <h3>Devil attitude</h3>
                            <p>Statement heels, darker styling and confident Goddess energy. Bring your presence, individuality and attitude &mdash; with your own limits leading the way.</p>
                        </div>
                    </article>
                </div>
                <p class="dad-note">You can explore either mood or combine both. Your look never decides what you have to offer. That choice stays with you.</p>
            </div>
        </section>

        <section class="dad-tinted">
            <div class="dad-wrap">
                <div class="dad-intro">
                    <span class="dad-eyebrow">Make it yours</span>
                    <h2>Your personality.<br>Your brand.</h2>
                    <p>Discover a creative direction that fits your interests, comfort and confidence. Start with one style or bring a few together.</p>
                </div>
                <ul class="dad-styles" aria-label="Creator styles">
                    @foreach (['Barefoot Angel', 'Sporty Brat', 'Spoiled Princess', 'Stiletto Goddess', 'Devil Doll', 'Alternative Doll'] as $style)
                        <li>{{ $style }}</li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section>
            <div class="dad-wrap dad-editorial">
                <img src="{{ asset('images/doll-after-dark/lifestyle.jpg') }}" width="1824" height="2288" loading="lazy" alt="Kayla in a grey coat, photographed in a car">
                <div>
                    <span class="dad-eyebrow">Personality meets practical skills</span>
                    <h2>You bring the ideas.<br>Learn the business.</h2>
                    <p>Build your direction with practical training designed to help you move from inspiration to a clear starting plan.</p>
                    <ul class="dad-points">
                        @foreach (['Develop your creator identity and page', 'Plan content with a simple setup', 'Build clear offers and understand pricing', 'Write captions and communicate confidently', 'Promote your page and stay organised', 'Protect your privacy and boundaries'] as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <section class="dad-join" id="join">
            <div class="dad-wrap">
                <div class="dad-intro">
                    <span class="dad-eyebrow">Choose your way in</span>
                    <h2>Build independently.<br>Or build with support.</h2>
                    <p>Two routes into Doll After Dark, depending on how you want to run your brand.</p>
                </div>
                <div class="dad-routes">
                    <article class="dad-route">
                        <span class="dad-eyebrow">The course</span>
                        <h3>Your brand, your way.</h3>
                        <p>Learn at your own pace and manage your business independently. The course is in preparation and is not available to purchase yet.</p>
                        <ul>
                            <li>Structured training and practical exercises</li>
                            <li>Develop your own content direction</li>
                            <li>Manage your page and customer conversations</li>
                        </ul>
                        <button class="dad-btn" type="button" disabled>COURSE COMING SOON</button>
                    </article>
                    <article class="dad-route dad-featured" id="agency">
                        <span class="dad-eyebrow">The agency</span>
                        <h3>A team behind your ideas.</h3>
                        <p>Training included, ongoing guidance and a dedicated chat team as part of your agreed agency package.</p>
                        <ul>
                            <li>Support with persona and content direction</li>
                            <li>Approved offers and boundaries agreed together</li>
                            <li>Responsibilities and revenue share explained before joining</li>
                        </ul>
                        <a class="dad-btn" href="{{ route('apply') }}">APPLY TO THE AGENCY</a>
                    </article>
                </div>
            </div>
        </section>

        <section>
            <div class="dad-faq">
                <div class="dad-intro">
                    <span class="dad-eyebrow">A few things to know</span>
                    <h2>Your questions, answered.</h2>
                </div>
                @foreach ([
                    'Do I need previous experience or a following?' => 'No previous experience or established following is needed to start learning. Building your brand takes practice, time and consistency.',
                    'Do I have to show my face or create nude content?' => 'No. You can explore faceless and non-nude approaches. Platform identity-verification requirements still apply.',
                    'Do I have to choose Angel or Devil?' => 'No. They are moods to explore. You can choose one, combine both or develop your own style.',
                    'Is joining the agency compulsory?' => 'No. The standalone course is a separate route for women who want to manage their own page. The course is coming soon.',
                    'Are earnings guaranteed?' => 'No. Results depend on your content, audience, consistency, time and effort.',
                ] as $question => $answer)
                    <details><summary>{{ $question }}</summary><p>{{ $answer }}</p></details>
                @endforeach
            </div>
        </section>

        <section class="dad-last">
            <span class="dad-eyebrow">Doll After Dark</span>
            <h2>Find your Doll energy.</h2>
            <p>Heavenly feet. Devilish heels.<br>Learn the business of soles and stilettos.</p>
            <a href="#join" class="dad-btn">CHOOSE YOUR NEXT STEP</a>
        </section>
        <div class="dad-disclaimer dad-wrap">
            <p>For women aged 18+. All interactions must be consensual.</p>
            <p>Platform rules apply. Earnings are not guaranteed. Agency terms apply.</p>
        </div>
    </div>
</x-layouts.marketing>
