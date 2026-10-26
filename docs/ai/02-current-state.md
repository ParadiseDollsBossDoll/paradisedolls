# Current State

## Multistreaming Comparison Removal (2026-10-27)

- Removed the client-identified Premium/freemium comparison heading and two cards from the public template and their editor field definitions. Other Multistreaming sections remain unchanged.
- Old stored values are retained but no longer rendered; no production data deletion or migration. Tests cover defaults and old saved content. Not deployed.
- User confirms Doll After Dark now appears in the live Site Editor.

## Doll After Dark Site Editor (2026-10-27)

- Adds Doll After Dark to Site Editor with page copy, four image uploads/alt text, lists, FAQ rows and button labels using existing marketing settings. Existing public defaults and layout are preserved.
- Application link and section anchors stay fixed; course button remains disabled. No checkout, schema changes, new endpoints or new dependencies. Not deployed.
- Related editor/public/draft-page HTTP tests cover saved content, uploads, isolation, authorization and escaped output. Browser editor checked using generated disposable admin HTML; saving covered by HTTP integration tests.
- Prior review-dropzone release at 42752c2 was activated per VPS output. PHP-FPM restart and homepage HTTP 200 were confirmed in user screenshots after the security package check; this is not a compromise audit.

## Review Photo Dropzone (2026-10-22)

- Success Stories Site Editor adds a multi-file picker and drag-and-drop target. Both append one existing-format upload/title row per accepted image; Add Review and existing reviews are retained.
- JPG/JPEG, PNG and WEBP, maximum 5 MB each; backend validation remains authoritative. Normal server request/upload limits still apply, so large collections may need separate saves.
- No new packages, migrations or upload endpoints. Prepared locally, not deployed.

## Review Upload Batches (2026-10-22)

- Site Editor's Success Stories review-photo section now has Add Review, which appends uniquely indexed upload/title rows before one Save Site Content submission. Other card editors are unchanged.
- Validation failures retain all submitted review titles/rows; file inputs must be selected again as normal browser behavior. Existing 5 MB/type validation and admin authorization remain unchanged. No schema migration or backend endpoint added.
- Tests cover five uploads in one save, existing reviews/home copy preservation, and invalid-batch recovery. Browser UI checked with disposable, generated admin HTML and five selected files; save handling checked by HTTP integration tests. Not deployed.
- Prior four-review release and HTTPS old-domain redirects are confirmed by user/server output. Old Bunny/Reverb secrets are revoked per user confirmation; historical scanner entries remain in Git history.

## Four Additional Review Images (2026-10-22)

- Added original Ruby, Precious, Jhon Paul, and Alina images to the shared homepage/Success Stories gallery defaults. Existing five images remain unchanged. No layout changes.
- Additive content migration appends missing new images to an existing saved review list without replacing custom reviews, titles, or other Site Editor content. Default-backed lists remain default-backed. Rollback intentionally retains approved review content.
- Not deployed. The previous Doll After Dark release at `71d6414` was activated and its public page/application link were checked live.

## Doll After Dark Page (2026-10-20)

- New `/doll-after-dark` page follows the client-supplied draft layout and four images, reusing the shared marketing navbar/footer. Explore links to the page. Agency CTA uses the existing application route; the course is Coming Soon, without checkout or a fake waitlist.
- Isolated Blade/CSS; no settings overwrite, migrations, or application workflow changes. Local review only; not deployed.
- Prior dependency/admin release at `13def4b` was activated in the supplied VPS screenshots; user reports everything works without errors. Earlier pending-release notes below describe historical pre-deployment state, not the current live status. Credential rotation is user-reported, not independently verified.

## Dependency Security Update (2026-10-20)

- `codex/frontend-dependency-security` updates PDF.js to 6.4.299 and compatible locked dependencies, with PDF rendering/audit CI checks. No production changes.
- Full npm audit: 16 -> 7 findings; no critical findings remain; production dependency graph reports zero. User chose to defer the Tailwind 4 migration; seven build-tool findings remain documented in `docs/dependency-security-update.md`.
- The VPS build staged from 8421a63 is obsolete for this candidate. Credential rotation is user-reported; video/chat confirmation and remaining deployment gates are still pending.

## Release Checks (2026-10-20)

- Candidate `codex/notification-read-styles` includes CI/manual deployment, the course verification fix, unseen sidebar badges, and read-notification styling. No backend release has been performed.
- Preflight confirms pushed code and passing exact-commit CI, but live `version.json` is missing. The VPS baseline, migration state, services, current backups, and rollback still require fresh server confirmation.
- Whole-release review found the new badge table could be absent during deployment. Badges now temporarily return zero and skip acknowledgement until the table exists, keeping admin pages available. The additive migration remains required to enable badge tracking.
- Historical Bunny API and Reverb private-secret exposures remain unresolved; user confirmed rotation is not done or uncertain. Never print these values or treat a candidate-only scan as clearing historical exposure.

## Admin Sidebar Badges (2026-10-20)

- Prepared on `codex/admin-unseen-badges`, not deployed: sidebar badges count pending items updated since each administrator last opened that section's list. Successful list visits acknowledge the section, including paginated/filtered lists; no approval or workflow status changes. Detail views do not acknowledge the entire section.
- New `admin_sidebar_visits` table stores per-admin section snapshots (record fingerprints, not private record contents). Do not release the PHP/Blade changes before running the additive migration. Pending totals inside pages and notification-bell read status remain unchanged.

## CI Setup In Progress (2026-10-20)

- `codex/ci-safety-checks` starts at the VPS code commit `f68e6be`, without the unfinished domain-integration changes. It adds isolated Linux syntax/test/build checks and makes deployment manual-only on main. See `docs/ci.md`.
- User screenshots show the new domain serving HTTP 200 over HTTPS directly from Hostinger, with new-domain cookies. Hostinger now manages DNS; earlier Cloudflare cutover plans are not the current production setup.
- User screenshots confirmed the new-hostname frontend manifest was published and the homepage returned HTTP 200. Backend candidate changes remain unshipped; this does not prove live chat works.
- First full-suite runs caught an existing enrolled-model verification bypass. The CI branch includes a narrow middleware ordering fix, not deployed; the earlier frontend-only approval does not authorize publishing this backend change.

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
