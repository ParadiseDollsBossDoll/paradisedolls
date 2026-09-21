# Change Log

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
