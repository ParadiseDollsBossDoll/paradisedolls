# Current State

## CI Setup In Progress (2026-10-20)

- `codex/ci-safety-checks` starts at the VPS code commit `f68e6be`, without the unfinished domain-integration changes. It adds isolated Linux syntax/test/build checks and makes deployment manual-only on main. See `docs/ci.md`.
- User screenshots show the new domain serving HTTP 200 over HTTPS directly from Hostinger, with new-domain cookies. Hostinger now manages DNS; earlier Cloudflare cutover plans are not the current production setup.
- A new-hostname frontend build is staged on the VPS, not published. CI and the remaining deployment gate must finish before publishing; no database content is changed by this CI setup.

Last updated: 2026-10-18

## Production Context

- Main production domain: `https://getrichwithparadisedolls.com`.
- Old `.net` domain redirects to `.com`.
- Production server path: `/var/www/paradisedollz`.
- Hostinger VPS uses Nginx, PHP-FPM, Supervisor queue/scheduler/Reverb services.
- Google Analytics and Google Tag Manager are installed through environment/configured partials.
- Prepared on `codex/withdraw-unapproved-marketing`, not deployed: withdraw Webcam Jobs, Guides, four dedicated guides, Webcam Agency, and Model Agency pending client/head-office approval. Direct URLs return 404 with noindex; navigation/footer and sitemap omit them.
- Content remains in Blade source and Git history for private review. Work From Home and Multistreaming are restored to their pre-guide-cluster templates from `5bee4c8^`; existing site-editor values remain in use.
- Client/head-office approval is required before publishing or restoring marketing copy. Earlier September SEO publication decisions are superseded by this withdrawal.
- Production remains unchanged until an explicitly authorized deployment. Pushing `main` triggers production deployment; use the review branch.

## Recent Work In Progress

- Two empty editable pages are available in Site Editor as Blank Page 1 and Blank Page 2. Both default to draft; public URLs return 404 until published, and admin previews are private/noindex. Page uploads use protected storage, with access withdrawn when unpublished. No public navigation/sitemap links are added. This feature is separate from domain cutover and preserves existing homepage content.

- The homepage hero has a dedicated responsive composition for the current five-person Boss Doll image: desktop retains the full group, while mobile centers the main three people and places database-managed copy below the image so it does not cover faces. A single-dot site-editor title placeholder is not rendered. The layout change does not seed or replace Kayla's saved site-editor content.
- The visible product brand is being changed to Boss Doll Creators using the supplied logo kit, Boss Pink (`#FF0080`), Doll Blush (`#FFE6F0`), Warm Cream (`#FFF7EF`), Deep Ink (`#29212A`), and DejaVu Sans typography.
- DejaVu Sans Regular and Bold are now self-hosted under `public/fonts/dejavu` and loaded by the shared stylesheet, so public, guest, member, chatter, admin, and community layouts no longer depend on an unavailable system-font fallback.
- Shared layouts opt into one restrained premium material system: glossy Boss Pink primary actions, glass-like secondary controls and panels, clearer focus states, short hero reveals, and reduced-motion fallbacks.
- Marketing pages with image heroes render the fixed header fully transparent at the top, switch to the solid blurred header after scrolling or opening navigation, and use shared responsive hero padding so copy and CTA rows do not crowd the following section.
- All shared layouts use a dedicated square Boss Doll Creators favicon built around the glossy pink heart/B mark; the wider brand mark remains available for larger display contexts but is no longer squeezed into browser-tab dimensions.
- During the transition, retain the existing `getrichwithparadisedolls.com` domain, email addresses, social URLs, VPS/database identifiers, and Paradise Dolls legal agreement wording until Kayla supplies approved replacements.
- A data migration updates database-backed marketing/course copy and the saved site theme; it deliberately does not rewrite legal or technical identifiers.
- Public primary actions now use one contrast-aware Boss Pink button style, and runtime theme colors calculate a readable foreground and hover color instead of using Doll Blush as the hover state. Light guest/authentication screens use the pink Boss Doll logo; dark surfaces retain the white logo.
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
