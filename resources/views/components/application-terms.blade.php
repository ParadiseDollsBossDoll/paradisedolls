<div class="rounded-md border border-boss-pink bg-boss-cream/70 p-4">
    <details class="group">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left">
            <span>
                <span class="block text-[0.68rem] uppercase tracking-[0.18em] text-boss-rose">{{ __('Terms & Conditions') }}</span>
                <span class="mt-1 block text-[0.88rem] font-semibold text-boss-dark">{{ __('Paradise Dolls application agreement') }}</span>
            </span>
            <svg class="h-4 w-4 shrink-0 text-boss-dark/35 transition-transform group-open:rotate-180" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 6l4 4 4-4"/>
            </svg>
        </summary>

        <div class="mt-4 max-h-80 space-y-5 overflow-y-auto rounded-md border border-boss-pink/50 bg-white/80 p-4 text-[0.8rem] leading-6 text-boss-dark/72">
            <p class="font-semibold text-boss-dark">{{ __('By submitting my application, I confirm and agree that:') }}</p>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Eligibility & Application') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('I am at least 18 years of age and legally entitled to work as an online creator.') }}</li>
                    <li>{{ __('All information I have provided is true, accurate and complete.') }}</li>
                    <li>{{ __('Submitting an application does not guarantee acceptance, and Paradise Dolls reserves the right to accept or decline any application at its sole discretion.') }}</li>
                    <li>{{ __('If my application is successful, I agree to complete all required identity verification, onboarding procedures and sign any additional agreements before commencing work.') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Self-Employed Status') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('I understand and acknowledge that I will be working as a self-employed online creator.') }}</li>
                    <li>{{ __('I am solely responsible for registering as self-employed where required by law and for paying my own Income Tax, National Insurance contributions and any other taxes or statutory liabilities arising from my earnings.') }}</li>
                    <li>{{ __('Paradise Dolls and Lux Dolls LTD are not responsible for deducting, paying or administering my taxes, National Insurance contributions or any other personal financial obligations.') }}</li>
                    <li>{{ __('I agree to indemnify and hold harmless Paradise Dolls and Lux Dolls LTD against any liability arising from my failure to meet my own tax or legal obligations.') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Agency Services') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('Paradise Dolls acts as my management agency by providing onboarding, training, mentoring, account management, marketing, technical support, promotional services and business development.') }}</li>
                    <li>{{ __('Paradise Dolls cannot guarantee any level of earnings or success, as results depend on my own effort, consistency, performance and market conditions.') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Training Programme') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{!! __('The Paradise Dolls Training Programme has a retail value of &pound;1,799 and may be purchased separately if I choose not to join the agency.') !!}</li>
                    <li>{{ __('If I choose to join Paradise Dolls as an agency model, the Training Programme is provided without any upfront cost on the condition that I remain with the agency for a minimum term of six (6) months.') }}</li>
                    <li>{!! __('If I choose to leave the agency before completing the minimum six (6) month term and continue working independently or with another agency, I agree to reimburse Lux Dolls LTD &pound;1,799, representing the value of the Training Programme, onboarding, mentoring, systems and business support provided.') !!}</li>
                    <li>{!! __('Any monies lawfully owed to Lux Dolls LTD, including authorised advances, agreed repayments or the &pound;1,799 Training Programme fee, may be recovered through an agreed repayment plan or any lawful debt recovery process.') !!}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Confidentiality & Intellectual Property') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('I agree to keep confidential all Paradise Dolls business information, systems, workflows, pricing, documents, templates, scripts, training materials and operational procedures.') }}</li>
                    <li>{{ __('I will not copy, reproduce, distribute, licence, sell, publish or otherwise use any Paradise Dolls training materials, systems, branding, documents or intellectual property without the prior written consent of Lux Dolls LTD.') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Non-Compete & Non-Solicitation') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __("During my relationship with Paradise Dolls and for twelve (12) months after it ends, I will not establish, operate or assist a competing creator management agency using Paradise Dolls' confidential information, systems, training materials or business methods.") }}</li>
                    <li>{{ __("I agree not to recruit, encourage or solicit Paradise Dolls' models, staff, chatters, contractors or business partners during my relationship with the agency or for twelve (12) months after it ends.") }}</li>
                    <li>{{ __("I understand that I am free to work independently or with companies and platforms that are not currently partnered with or managed by Paradise Dolls, provided this does not create a conflict of interest, breach any exclusivity arrangements or involve the use of Paradise Dolls' confidential information, systems or training materials.") }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Professional Standards') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('I agree to conduct myself professionally at all times.') }}</li>
                    <li>{{ __('I agree to comply with all Paradise Dolls policies, procedures, operational guidance and the rules of any platforms I use whilst working with the agency.') }}</li>
                    <li>{{ __('Paradise Dolls may suspend or terminate my relationship with the agency if I breach these Terms & Conditions, the Model Agreement or any applicable platform rules.') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Security & Verification') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('I agree to keep all account login details, passwords and security information confidential.') }}</li>
                    <li>{{ __('Paradise Dolls may request additional identity verification or documentation at any stage to comply with legal, platform or security requirements.') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Payments') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('Payments are based on earnings received from the relevant platforms and may be adjusted to reflect refunds, chargebacks, platform deductions, authorised advances or other agreed deductions.') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Data Protection & Privacy') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('I consent to Paradise Dolls collecting, storing and processing my personal information, identification documents and payment information for the purposes of reviewing my application, identity verification, onboarding, compliance, payment processing and ongoing account management.') }}</li>
                    <li>{{ __('My personal information will be processed securely in accordance with applicable UK data protection legislation and the Paradise Dolls Privacy Policy.') }}</li>
                    <li>{{ __('My information will only be shared where required for legal, regulatory, payment or platform compliance purposes.') }}</li>
                    <li>{{ __('I consent to Paradise Dolls securely retaining my verification records and identification documents for as long as required by law or for legitimate business and compliance purposes.') }}</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h4 class="text-[0.66rem] font-semibold uppercase tracking-[0.16em] text-boss-rose">{{ __('Electronic Acceptance') }}</h4>
                <ul class="list-disc space-y-1 pl-5">
                    <li>{{ __('Ticking the acceptance box constitutes my electronic signature and has the same legal effect as signing a paper agreement.') }}</li>
                </ul>
            </section>
        </div>
    </details>
</div>
