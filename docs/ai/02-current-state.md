# Current State

Last updated: 2026-09-22

## Production Context

- Main production domain: `https://getrichwithparadisedolls.com`.
- Old `.net` domain redirects to `.com`.
- Production server path: `/var/www/paradisedollz`.
- Hostinger VPS uses Nginx, PHP-FPM, Supervisor queue/scheduler/Reverb services.
- Google Analytics and Google Tag Manager are installed through environment/configured partials.
- The public marketing site has dedicated organic SEO landing pages for `webcam jobs`, `webcam agency`, and `model agency`, plus a Guides hub and focused beginner guides for becoming a webcam model, safety/privacy, agency-vs-independent decisions, and earnings/payments.
- The Work From Home and Multistreaming pages now target their own search intents with complete practical guidance, FAQ structured data, and links into the guide cluster.
- The marketing topic cluster uses one primary intent per URL, visible people-first guidance, candid adult/self-employed/privacy/income caveats, responsive guide layouts, canonical metadata, breadcrumb/FAQ structured data where supported, and sitemap coverage.
- The Webcam Jobs page is a people-first applicant guide covering the adult creator role, 18+ eligibility, self-employed status, requirements, agency support, application steps, earning limitations, and FAQs. It includes Organization, WebPage, BreadcrumbList, and FAQPage structured data; JobPosting markup is intentionally withheld until a specific verifiable vacancy can provide every required field.
- The Webcam Agency page is a people-first agency guide covering services, model/agency responsibilities, adult and self-employed status, founder experience, application steps, training commitment, earning limitations, internal guidance links, and supported Organization, WebPage, BreadcrumbList, and FAQPage structured data.
- The Model Agency page targets the accurate international-facing `online model agency` category. It distinguishes Paradise Dolls from fashion casting, explains country/platform eligibility, services, self-employed status, founder experience, application and training terms, agency-comparison questions, and supported Organization, WebPage, BreadcrumbList, and FAQPage structured data.
- The public marketing header uses a focused desktop navigation with secondary discovery pages grouped under Explore, including the Guides hub, plus a scroll-safe responsive menu for tablet and mobile.

## Recent Work In Progress

- Email campaign audiences were expanded to separate application/onboarding/readiness/working-status groups.
- Model profiles now distinguish:
  - Fully onboarded: real onboarding completion or manual full onboarding.
  - Active model: fully onboarded and currently working.
  - Inactive model: no longer working or decided not to continue.
  - Approved websites: checklist of platforms/sites the model has been approved or signed up for.
- Chatter system has account management, clock tracking, payroll summaries, role/rate assignments, exports, and review workflows.
- Admin Chatter Hours account management keeps account employment status separate from live working status; the account page has a Working now panel based on open shifts.
- Chatter account setup now requires name, email, timezone, role, and fixed USD/hr rate. Role and rate stay paired on chatter role assignments and are frozen onto shifts at clock-in.
- Chatter clock-in now requires the website/platform being worked on. Payroll remains fixed-rate and flexible-hours; overtime, premium rates, and night windows are no longer active payroll inputs.
- Chatter clock-in also captures the assigned model being worked with when the chatter has active model assignments; shifts, dashboards, timesheets, payroll snapshots, and exports preserve the selected website/platform and model.
- Chatter clock-in now requires the model's starting earning balance and chatter clock-out requires the ending earning balance. Token platforms use token counts; Babestation uses GBP amounts.
- Chatter commission is 3% of generated shift earnings using platform-specific values frozen on the shift: Stripchat/SC, Chaturbate/CB, Camsoda, and MyFreeCams/MFC at $0.05/token; CAM4 at $0.10/token; Bongacams at $0.025/token; Babestation directly in GBP.
- Chatter payroll separates hourly base pay, USD token-site commission, Babestation GBP commission, manual adjustments, and final USD/PHP pay in admin review and Excel export while preserving approved snapshot protection. Babestation GBP commission is converted into USD using an automatic GBP/USD reference rate, then included in total USD and PHP payroll.
- Admin Chatter Hours attendance/payroll defaults to the current Europe/London payroll week; older weeks remain available through explicit date filters, and exports follow the same default/filter behavior.
- Admin chatter attendance, payroll, timesheet review, and Excel export show shift balance in/out, generated earnings, and commission. For older unapproved snapshot rows where generated earnings exist but stored commission is zero, display/export recovers the commission from the generated earnings and frozen commission basis points without mutating approved payroll snapshots.
- Chatter payroll uses Europe/London weeks from Monday 12:00 AM through Sunday 11:59 PM UK time. Real shifts that cross the Sunday-to-Monday cutoff stay open/continuous, but payroll, admin attendance, timesheet review, and exports use virtual clipped segments for the relevant week or date range.
- Chatter timesheets now use an admin-driven weekly workflow: current weeks are In progress, finished unapproved weeks are automatically Ready for review, and admin/Kayla approves payroll after required model reviews are complete. Chatters report problems for admin follow-up instead of submitting or reopening timesheets.
- Admin Chatter Hours tables and Excel exports omit zero-work/zero-pay payroll periods and zero-minute shifts, while preserving records with paid minutes, adjustments, or final pay and keeping hidden timesheets directly accessible.
- Admin/Kayla can manually unlock specific published academy courses for individual chatter accounts. Chatters only see courses that were manually unlocked for them, using the same lesson/player and protected media flow as enrolled members.
- Chatter/model review workflows are private and intended for admin review, not public display.
- Admin/Kayla can prepare weekly model draws by creating a draw, importing or adding model earnings, marking qualified models, exporting qualified names for Wheel of Names, managing prizes, and recording the winner/prize/OBS recording link after the live draw.

## Working Rules For The Next Change

- Read `00-ai-project-map.md`, this file, and the relevant feature entry before editing.
- For database changes, add migrations and include `php artisan migrate --force` in VPS instructions.
- For Blade/CSS/JS changes, run `npm run build` and include it in VPS instructions.
- For route or view changes, run `php artisan route:cache` and `php artisan view:cache` checks where possible.

## Known Watch Points

- Do not commit `.env` files or API keys.
- Keep `.obsidian/workspace.json` uncommitted because it is personal layout state.
- Email campaign raw applicant delivery currently depends on a real `User` for safe unsubscribe handling.
- Fully onboarded and active are intentionally different states.
- Break time must never count toward chatter worked hours.
- Chatter shifts that cross the UK payroll cutoff must be split only in reporting/payroll output, not by creating automatic real clock-out/clock-in records.
- Phase 1/2 earning commission is calculated for the real shift and counted in the payroll week containing the real clock-out time; future exact split commission would need earning checkpoints at the UK cutoff.
- Approved chatter payroll snapshots remain immutable unless admin explicitly reopens the week; chatter problem reports never reopen payroll.
- Weekly model draws are admin-only and currently use manually entered/imported earnings; there is no in-app roulette wheel or automatic model earnings integration yet.
