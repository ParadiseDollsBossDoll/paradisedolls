# Change Log

## 2026-10-22 - Four Supplied Review Images

- Added unchanged Ruby/Precious/Jhon Paul/Alina image files and gallery defaults. Shared gallery displays them on homepage and Success Stories.
- Added an idempotent saved-content migration and regressions for both pages, preserving custom content and existing reviews. No production update yet.

## 2026-10-20 - Doll After Dark Reference Page

- Added a separate public page matching the client-provided draft's section order, imagery, serif headings, paired persona panels, FAQ, and dark course/agency section. Inherits the Boss Doll Creators navbar/footer and appears under Explore.
- Agency CTA uses the existing application route; course marked Coming Soon with no active purchase/waitlist. Preserved saved Site Editor values and all application processing. Not deployed.

## 2026-10-20 - Focused Frontend Dependency Security Update

- Updated PDF.js across its security-fix major boundary and refreshed compatible locked dependencies without force-upgrading Tailwind. Preserved custom canvas rendering through the documented PDF.js 6 API.
- Added a patched-version regression (observed failing before update), PDF parse/text/pixel smoke coverage, and production dependency audit in CI. Remaining seven build-tool findings are explicitly deferred by the user, not suppressed. Not deployed.

## 2026-10-20 - Badge Migration Compatibility

- Deployment review reproduced admin HTTP 500s when the new badge table is absent. Added a narrow schema-availability guard: hide badges and skip acknowledgement until the migration is applied, without swallowing other database errors.
- Regression test first failed with the table removed, then checks both dashboard and application-list availability and unchanged pending status. Release remains blocked on historical credential triage and fresh VPS preflight evidence; nothing deployed.

## 2026-10-20 - Notification Read-State Styling

- Read notification titles use regular weight and muted text in the bell dropdown and notifications page. Unread titles remain semibold with a dot; read dots are hidden without changing alignment. Existing read/open actions remain unchanged.
- Added a rendered-HTML regression test for both views, observed failing before the styling change. Not deployed.

## 2026-10-20 - Per-Admin Unseen Sidebar Badges

- Replaced shared pending-work sidebar counts with per-admin unseen counts. Successful section-list visits acknowledge current pending items; new or updated pending records reappear. No approvals, rejections, payroll states, or notification-bell read flags are changed.
- Added an isolated visit table, guarded admin acknowledgement, and regression coverage for admin isolation, new records, updates, out-of-order tabs, and payroll-week readiness without row writes. Production deployment remains pending.

## 2026-10-20 - Isolated CI Checks

- Added a read-only GitHub CI job for PHP/JavaScript syntax, Laravel tests against disposable SQLite, and locked-dependency frontend builds. Added workflow regression checks, first observed failing before implementation.
- Removed automatic production deployment on main pushes; the deployment workflow is manual-only and main-only. CI setup is not a production release.
- The first local and Linux full-suite runs reproduced an existing course verification bypass. Moved verification ahead of the enrollment allow rule, retaining the existing failing regression test. This application fix is not deployed.

## 2026-10-19 - Homepage Review Photo Marquee

- Homepage review photos move right to left in a seamless strip, preserving equal frames and full-size image links. Added pause checkbox, hover/focus pause, inert duplicate images, and manual scrolling when reduced motion is enabled. Success Stories retains the static gallery.

## 2026-10-19 - Uniform Review Gallery On Homepage And Reviews

- Shared one responsive photo gallery across homepage and Success Stories. Equal 4:5 frames are capped at 340px wide with uncropped contained images and full-size links. Added subtle hover lift and progressive scroll reveal, disabled for reduced motion. Removed the homepage's old duplicated text-review carousel and unused testimonial query; existing review records remain intact.

## 2026-10-19 - Client Review Photo Gallery

- Replaced text testimonial cards on the public Success Stories page with five supplied review photos, displayed uncropped with full-size links. Site Editor supports additional/reordered review photos through the Success Stories fields. Existing testimonial records and member/admin workflows are preserved; no database migration or saved-copy replacement is required.

## 2026-10-19 - Restore Blank Page Layout And Add Navigation

- Restored the original blank-page layout with banner followed by logo, title, and content. Published pages appear under Explore on desktop and in mobile navigation using their saved title; drafts remain hidden. Existing saved fields are not deleted.

## 2026-10-19 - Draft Page Image Heroes

- Both editable draft pages now use the existing marketing image-hero layout with transparent top navigation, overlaid page title, optional eyebrow, logo, and separate hero paragraphs. Introduction and image/text sections remain below the banner, matching the Our Story composition. Saved content and draft/publication controls are preserved.

## 2026-10-19 - Two Editable Draft Pages

- Added Blank Page 1 and Blank Page 2 to Site Editor with empty logo, banner, title, introduction, and repeatable content sections. Both default to unpublished and public requests return 404; admin-only previews are private/no-store and noindex.
- Publishing requires a title and exposes a public link in Site Editor; unpublishing hides the URL again. No public navigation or sitemap links are added. Existing homepage settings are preserved. Uses existing site_settings storage; no database migration required.

## 2026-10-18 - Application Copy Approved By Client

- Updated public agreement and acceptance text to Boss Doll Creators and changed the message placeholder to the client's supplied wording. No saved marketing content or agreement obligations were changed.

## 2026-10-18 - Responsive Homepage Hero Prepared

- Added the client-supplied extended lounge image as the default hero, removed the separate navbar gap, and adjusted mobile framing for the extra image headroom. Production's saved hero image remains editable and must be selected through Site Editor; no saved text is overwritten.
- Added homepage-specific responsive art direction for the current Boss Doll Creators group image: the full five-person composition remains visible on desktop and the central three-person composition is prioritized on phones.
- Moved all hero copy below the photograph, stacked phone actions within the viewport, prevented horizontal overflow, and suppressed the single-dot title placeholder without changing Kayla's database-managed wording or uploaded image.
- Added regression coverage confirming saved site-editor hero content remains authoritative.

## 2026-10-17 - Boss Doll Creators Rebrand Prepared

- Replaced visible Paradise Dolls naming across public pages, application areas, emails, admin/member/chatter layouts, exports, seeded content, and metadata with Boss Doll Creators.
- Added the supplied Boss Doll Creators horizontal and compact logos, centralized brand configuration/logo rendering, and applied the approved pink, blush, cream, white, and deep-ink palette with DejaVu Sans typography.
- Unified public primary actions around a contrast-aware Boss Pink button, fixed washed-out dynamic hover colors, improved hero sizing and strategy-step readability, and made guest/authentication logos match their light or dark surface.
- Bundled DejaVu Sans Regular and Bold locally, applied them through every shared layout, and rebalanced the home hero headline around the brand font instead of the browser fallback.
- Added a shared premium UI treatment for glossy pink actions, secondary controls, panels, fields, badges, navigation surfaces, and short hero reveals with accessible focus and reduced-motion behavior.
- Restored the marketing header's fully transparent top state over image heroes while retaining the polished solid header after scrolling or opening navigation.
- Added shared responsive image-hero padding so descriptions and CTA rows have consistent breathing room above the next section on desktop and mobile.
- Replaced the squeezed wide browser-tab icon with a dedicated square glossy pink heart/B favicon and connected it through the centralized brand configuration across every shared layout.
- Added a migration for saved marketing content, course content, and site-theme values while retaining the existing domain, email addresses, social URLs, infrastructure identifiers, and legal agreement wording pending confirmed replacements.
- Added regression coverage for visible branding, supplied logo usage, and retained contact/social identifiers.

## 2026-10-04 - Marketing Withdrawal Prepared (Not Live)

- Prepared eight SEO/guide URLs to return 404 with noindex, removed their navigation/footer links and sitemap entries, and restored Work From Home and Multistreaming from `5bee4c8^`. Templates/history are retained for review. Added direct GET/HEAD, public-link, sitemap and established-page regression coverage. Deployment is pending; this entry does not claim a live takedown.

## 2026-09-22

- Added a public Webcam Model Guides hub with grouped paths for getting started, safety/support, and operations/earnings, plus CollectionPage, BreadcrumbList, and ItemList structured data.
- Added focused guides for how to become a webcam model, webcam model safety/privacy, webcam agency versus independent modelling, and webcam model earnings/payments.
- Rebuilt the Work From Home and Multistreaming pages as complete search-intent guides with practical sections, candid eligibility/privacy/income/platform caveats, FAQs, and structured data.
- Added a reusable responsive marketing guide layout, connected the guide cluster through the header, footer, breadcrumbs, commercial landing pages, related-guide navigation, and sitemap, and expanded SEO rendering tests.
- Rebuilt the Model Agency landing page around accurate online creator representation, including its distinction from fashion casting, remote country/platform eligibility, agency services, founder experience, application stages, comparison guidance, training terms, internal links, and expanded FAQs.
- Added Organization, WebPage, BreadcrumbList, and FAQPage structured data to the Model Agency page, updated its sitemap modification date, and added focused rendering tests.
- Rebuilt the Webcam Agency landing page as a detailed agency guide covering services, responsibilities, founder experience, application stages, transparent training terms, income limitations, internal links, and applicant FAQs.
- Added Organization, WebPage, BreadcrumbList, and FAQPage structured data to the Webcam Agency page, updated its sitemap modification date, and added focused rendering tests.
- Rebuilt the Webcam Jobs landing page around useful applicant information, including role expectations, eligibility, self-employed status, support, application steps, earning disclaimers, internal guidance links, and expanded FAQs.
- Added reusable marketing-layout support for social preview images and JSON-LD, then added Organization, WebPage, BreadcrumbList, and FAQPage structured data to the Webcam Jobs page.
- Updated the Webcam Jobs sitemap modification date and added SEO rendering tests.
- Added Home as the first public navigation destination and moved Webcam Jobs to the end of the direct navigation links.

## 2026-09-20

- Kept shared SEO-page hero actions inside the image with content-aware hero spacing and a bounded responsive CTA layout that stacks safely on phones.
- Polished the shared marketing header with a focused desktop navigation, an Explore menu for secondary pages, and a responsive scroll-safe mobile menu with larger tap targets.
- Added public SEO landing pages for `webcam jobs`, `webcam agency`, and `model agency`, with internal links, keyword-focused page copy, metadata, sitemap entries, and route tests.
- Expanded the marketing layout to support per-page meta descriptions, optional keyword metadata, Open Graph descriptions, and Twitter summary metadata.

## 2026-08-31

- Polished the Admin Chatter Hours attendance and weekly payroll tables by grouping related time, earning, commission, adjustment, and total-pay fields into more compact responsive rows.
- Compacted the Admin Chatter Hours attendance KPI strip so the summary cards are shorter, wrap more gracefully, and show GBP commission conversion without awkward line breaks.
- Added automatic GBP-to-USD conversion for Babestation chatter commission so GBP commission is still shown separately but is included in total USD pay and PHP payroll totals with approved snapshot protection.
- Added a GBP/USD payroll reference-rate service, configured fallback, timesheet conversion snapshot fields, admin payroll labels, and XLSX export output for converted GBP commission.
- Fixed chatter commission display/export so generated earning rows recover the 3% commission when older snapshot data has generated earnings but a zero stored commission value.
- Updated Admin Chatter Hours attendance/payroll to default to the current UK payroll week while keeping older weeks available through date filters and matching XLSX export filters.
- Fixed the Admin Chatter Hours Export Excel button to pass the resolved current UK payroll week dates, so a no-filter click exports the same week shown in the admin tables.
- Fixed chatter payroll XLSX filenames and "PAYROLL AS OF" labels to use the selected/default filter date range instead of expanding backward to the first overlapping timesheet week.
- Added a UK payroll time indicator to Admin Chatter Hours attendance controls so the current London date/time and active payroll-week range are visible beside the filters.
- Clarified Admin Chatter Hours payroll labels so final columns read as total pay in USD/PHP and the USD total explicitly shows base pay plus USD commission plus adjustments.
- Added balance in/out, generated earnings, and commission details to admin recorded work sessions, plus generated earning totals in weekly payroll rows and timesheet review summary cards.
- Reworked chatter commission tracking from token-only balances to platform-aware earning balances with stored shift snapshots for earning unit, currency, token/GBP value, generated earnings, and 3% commission.
- Added confirmed platform rates: Stripchat/SC, Chaturbate/CB, Camsoda, and MyFreeCams/MFC use $0.05 per token; CAM4 uses $0.10 per token; Bongacams uses $0.025 per token; Babestation uses GBP balances directly.
- Kept Babestation commission separate as GBP in admin review and Excel export instead of converting it into USD/PHP payroll totals without a confirmed GBP-to-USD rule.

## 2026-08-29

- Added chatter shift token tracking: chatters now enter starting tokens at clock-in and ending tokens at clock-out, with generated tokens stored on the shift.
- Added 3% chatter token commission calculation on completed shifts while preserving hourly pay, break exclusion, platform/model/role/rate snapshots, and approved payroll protection.
- Split chatter payroll display/export into base hourly pay, token commission, manual adjustments, and final pay; admin timesheet review and Excel export now include token/commission details.

## 2026-08-27

- Fixed the Admin Chatter Hours Working now panel text contrast so platform/model, clock-in, and role/rate details remain readable on the pale live-status card.
- Optimized the Admin Chatter Hours Working now panel into a compact, scroll-contained live roster with expandable details so many active chatters do not stretch the page.
- Polished the admin Chatter Hours account, live-working, account-manager modal, attendance, payroll, and timesheet review UI for cleaner scanning, long-label handling, responsive controls, and clearer account-vs-working status separation without changing payroll or clock behavior.
- Added a separate Admin Chatter Hours account-page "Working now" panel for open chatter shifts, showing live chatter, platform, selected model, UK clock-in time, and frozen role/rate context.
- Renamed the chatter account employment badge from raw "Active" wording to "Account active" / "Account suspended" so account access is distinct from live working status.

## 2026-08-24

- Split chatter payroll reporting at the UK Sunday-to-Monday cutoff without creating extra real clock events, so cross-week shifts are paid and displayed only for the portion inside each payroll week.
- Updated admin attendance, timesheet review, and Excel payroll export output to use the same clipped shift segments while preserving platform, model, work role, and frozen hourly rate context.
- Allowed admin approval of a completed payroll week when an open real shift continued past the cutoff, while keeping the active shift open.

## 2026-08-19

- Restored the Laravel Collection import required by the admin Chatter Hours timezone option builder, fixing the production account-manager type error.
- Hid zero-work chatter payroll periods and zero-minute shifts from Admin Chatter Hours tables and Excel exports while retaining adjustment/payable rows and direct timesheet access.
- Simplified chatter timesheets into an admin-driven workflow: current weeks show In progress, finished unapproved weeks show Ready for review, and chatter submission is no longer required or exposed.
- Moved required weekly model-review checks to admin payroll approval while preserving approved payroll snapshots and all existing payroll calculations.
- Replaced chatter correction requests with audit-only problem reports that notify admin without reopening or changing payroll, and added scheduled creation of missing weekly timesheets for active chatters.
- Added assigned-model selection to chatter clock-in, validated against active chatter/model assignments, and stored the selected model on recorded shifts.
- Displayed the selected model beside website/platform context in chatter dashboards, admin attendance, timesheet review, payroll snapshots, and payroll exports.
- Fixed the admin chatter timesheet shift summary Blade markup so role/rate, platform, and selected-model badges render without a template parse error.

## 2026-08-17

- Added an admin-only Weekly Model Draw feature with weekly draw creation, manual/imported model earnings, qualified model marking, plain-text Wheel of Names export, prize management, winner/prize recording, OBS recording link storage, and draw history.

## 2026-08-10

- Updated the admin chatter creation timezone combobox to show friendly UTC-offset labels while preserving valid PHP timezone identifiers as stored values.
- Added an editable friendly timezone combobox to existing chatter account manager modals, with backend timezone validation and audit tracking.
- Fixed existing chatter timezone edit layout so long friendly timezone labels truncate cleanly and the Save button wraps within the account manager modal.
- Added manual academy course unlocks for chatter accounts, chatter-only academy routes, protected media access via enrollment, and a chatter dashboard training panel.
- Hid course community links from chatter academy pages and prevented chatter course enrollments from granting course community chat access.
- Made existing chatter work-role assignments editable for role name, USD/hr rate, and clock-in availability while switching the chatter assignment instead of renaming shared roles.
- Polished the admin Chatter Hours assigned work roles editor so role/rate inputs, availability, and save actions align cleanly across desktop and smaller widths.
- Kept assigned work role rows in the side-by-side role/rate/save layout at narrower modal widths so local and production render consistently.

## 2026-08-09

- Simplified chatter account setup to name, email, searchable timezone, role, and fixed USD/hr rate.
- Paired chatter roles with their rates for account creation, request approval, account management, clock-in, payroll snapshots, and exports.
- Added a platform field to chatter clock-in and stored it on shifts for dashboards, admin attendance, payroll review, and payroll snapshots.
- Removed active overtime/night/weekend premium payroll calculations in favor of fixed shift role rates.
- Expanded the AI feature index into a KOS-style project map with 20 feature areas, start-here files, related files, notes, and watch points.
- Added root AI router files:
  - `AGENTS.md`
  - `CODEX.md`

## 2026-08-04

- Added starter AI documentation for Obsidian:
  - Project map
  - Feature index
  - Current state
  - Decisions log
  - Change log
- Added git ignore rule for Obsidian workspace layout state.
