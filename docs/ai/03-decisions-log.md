# Decisions Log

## 2026-09-22 - People-First Webcam Guide Topic Cluster

- Paradise Dolls will build organic visibility through a connected learning centre rather than placing every target keyword on one landing page.
- Each public guide owns one primary search intent to reduce keyword cannibalisation: getting started, work from home, safety/privacy, agency-versus-independent decisions, earnings/payments, and multistreaming.
- Guide content must explain adult eligibility, self-employed responsibilities, variable earnings, anonymity limits, platform-rule changes, and the need for qualified local tax/legal advice where relevant.
- Structured data must match visible content. The guide system uses WebPage, BreadcrumbList, FAQPage, CollectionPage, and ItemList types where supported and excludes invented authorship, review dates, ratings, earnings promises, and unsupported claims.
- Organic improvements cannot guarantee a number-one position. Search Console data, qualified applications, and query-level performance should guide later expansion after the new URLs have had time to be crawled and measured.

## 2026-09-22 - Accurate Global Model Agency Positioning

- The Model Agency landing page targets `online model agency` and related remote creator searches while explaining the broader `model agency` term accurately.
- Paradise Dolls must not imply that it offers fashion, runway, editorial, or commercial casting. Remote international applications are described as subject to age, legal, platform, payment, and agency eligibility rather than universally available.
- Global SEO work will use useful English content and legitimate authority building first. Localized indexable URLs and `hreflang` should only be added for countries/languages the agency genuinely supports with reviewed translations and country-appropriate information.
- Organization, WebPage, BreadcrumbList, and FAQPage structured data must match visible content; unverified reviews, casting credentials, locations, and guarantees are excluded.

## 2026-09-22 - People-First Webcam Agency SEO

- The Webcam Agency landing page answers applicant decision questions instead of relying on visible keyword lists, unsupported superlatives, or copied competitor claims.
- The page publishes the verified agency services, adult and self-employed nature of the work, shared responsibilities, application process, founder experience, training commitment, and lack of guaranteed earnings.
- Organization, WebPage, BreadcrumbList, and FAQPage structured data must remain aligned with visible page content. No review ratings, LocalBusiness details, or unverifiable claims are marked up.

## 2026-09-22 - People-First Webcam Jobs SEO

- The Webcam Jobs landing page prioritizes complete, candid applicant information over visible keyword lists or search-engine-facing copy.
- The page clearly describes adult creator work, 18+ eligibility, self-employed status, variable earnings, agency support, and application terms to align search intent with the real opportunity.
- Organization, WebPage, BreadcrumbList, and FAQPage structured data are supported by visible page content. `JobPosting` markup will not be added until Paradise Dolls has a specific current vacancy with accurate required fields such as posting dates, location, employment type, and compensation.

## 2026-09-20 - Public SEO Keyword Pages

- Paradise Dolls targets competitive Google searches through dedicated, useful public landing pages instead of keyword stuffing.
- The first organic SEO pages target `webcam jobs`, `webcam agency`, and `model agency` with visible page copy, page titles, meta descriptions, canonical URLs, footer/internal links, and sitemap entries.
- Organic SEO does not guarantee a number-one ranking; paid search remains a separate Google Ads strategy if immediate top placement is needed.

## 2026-08-31 - Babestation GBP Commission Conversion

- Babestation remains a GBP earning source: chatter clock-in/out balances, generated earnings, and commission are stored and displayed as GBP.
- Chatter payroll converts Babestation GBP commission into USD using an automatic GBP/USD reference rate, then includes that converted amount in total USD pay and PHP conversion.
- Approved chatter payroll snapshots store the GBP/USD rate, rate metadata, converted GBP commission amount, and final PHP total so later exchange-rate changes do not alter approved payroll.
- If the automatic GBP/USD provider is unavailable, payroll uses the protected configured fallback/last-known rate and marks the rate as fallback/stale in the same pattern as USD/PHP.

## 2026-08-31 - Chatter Commission Display And Report Defaults

- Admin Chatter Hours attendance/payroll views and XLSX exports default to the current Europe/London payroll week when no date filters are selected.
- Older chatter payroll and attendance records remain accessible and exportable through explicit `from` and `to` filters.
- Display/export code may derive commission from snapshot generated earnings and frozen commission basis points when an unapproved or stale snapshot row has generated earnings but a zero stored commission value.
- Approved payroll snapshots remain protected; correcting an approved stale commission still requires the existing reopen/refresh/reapprove flow.
- Weekly payroll tables show generated earning totals and commission totals, but balance in/out stays per shift to avoid misleading summaries across multiple shifts.

## 2026-08-31 - Platform-Aware Chatter Commission

- Chatter shift earning balances are platform-aware instead of token-only.
- Token sites store token counts and a frozen USD micro-value per token on the shift. Confirmed rates are Stripchat/SC, Chaturbate/CB, Camsoda, and MyFreeCams/MFC at $0.05/token; CAM4 at $0.10/token; Bongacams at $0.025/token.
- Babestation stores GBP balances in pence, calculates generated GBP and 3% GBP commission directly, then converts that commission into USD/PHP payroll totals through the automatic GBP/USD payroll reference rate.
- Unknown manually typed platforms currently fall back to the standard token-site rule of $0.05/token so existing flexible platform entry keeps working.
- Ending earning balance below starting earning balance is blocked at chatter clock-out; any future override needs an admin correction flow with audit reason.

## 2026-08-29 - Chatter Token Commission

- New chatter shifts require a starting earning balance at clock-in and an ending earning balance at clock-out.
- Generated shift earnings are calculated from the ending balance minus starting balance; if a legacy/admin-closed shift is missing either balance, commission is $0 for that shift.
- Chatter commission is calculated as 3% of generated shift earnings using the platform-specific snapshot stored on the shift.
- USD commission is stored separately from hourly base pay and manual adjustments; USD final gross pay is base hourly pay plus USD commission plus adjustments. Non-USD commission is stored separately.
- For Phase 1/2, full shift commission is counted in the payroll week that contains the real clock-out time. Exact week-split commission would require future cutoff earning checkpoints.

## 2026-08-24 - Chatter Payroll Week Cutoff

- Chatter payroll uses Europe/London payroll weeks that start Monday at 12:00 AM UK time and end Sunday at 11:59 PM UK time.
- Shifts that cross the Sunday-to-Monday cutoff remain one real clock session, but payroll, attendance display, timesheet review, and exports use virtual reporting segments clipped to the selected UK week/date window.
- Breaks are clipped to the same reporting segment before paid minutes are calculated, including breaks that cross the weekly cutoff.
- Approved payroll snapshots remain protected; historical unapproved periods recalculate with the split only when payroll is refreshed.

## 2026-08-19 - Admin-Driven Chatter Timesheets

- Chatter-facing timesheet status is derived as In progress for the current week, Ready for review for a finished unapproved week, Approved for approved payroll, or Closed for a legacy rejected record.
- Chatters do not submit timesheets. Admin/Kayla reviews and approves completed weeks directly, and required weekly model reviews are enforced at admin approval.
- Existing internal status values remain readable for legacy records, but Draft and Submitted are not chatter-facing workflow states.
- Reporting a timesheet problem creates an audit record and admin notification only; it does not reopen a timesheet, edit time, or alter an approved payroll snapshot.
- An idempotent daily scheduler command creates missing weekly timesheet records for active chatters, including zero-hour weeks.

## 2026-08-19 - Chatter Shift Model Attribution

- Chatter shifts may store a nullable `model_id` for historical/legacy rows, but new chatter clock-ins require a model whenever that chatter has active model assignments.
- Chatter users can only clock in against models actively assigned to their chatter account.
- Payroll snapshots keep the selected website/platform and model name/email as immutable shift context.

## 2026-08-17 - Weekly Model Draws

- Weekly model draws are admin-only operational records for Kayla/admin.
- Model earnings are added or imported manually per draw; the app does not calculate model earnings from another system yet.
- Qualified model names are copied/exported as plain text for Wheel of Names.
- The live spin stays outside the app in OBS using Wheel of Names; the app stores the winner, selected prize, and recording link after the draw.
- Member/model dashboards and workflows do not expose weekly draw prep or earnings records.

## 2026-08-10 - Manual Chatter Course Access

- Chatter academy access reuses `course_enrollments` as the manual unlock record.
- Chatters do not receive automatic access to all published courses; admin/Kayla must grant each course per chatter account.
- Chatter course routes reuse the existing member course display and protected asset controllers, but use chatter route names and skip member course access requests.
- Model/member course access remains verification plus enrollment/access-request based.

## 2026-08-10 - Chatter Role Assignment Edits

- Editing a chatter's assigned role name creates or switches that chatter's assignment to a role resolved by name.
- Editing one chatter assignment does not rename the shared `chatter_work_roles` record, so other chatter assignments and historical shift/payroll labels are not unexpectedly changed.
- New shifts use the chatter's current assigned role/rate pair after the edit.

## 2026-08-09 - Chatter Fixed Role Rates

- Treat each chatter's work role and USD hourly rate as a paired assignment.
- New chatter setup requires only name, email, timezone, role, and fixed USD/hr rate.
- Payroll uses the role/rate frozen onto each shift at clock-in; overtime, premium, night-start, and night-end settings are no longer part of active payroll calculation.
- Chatter shifts record the website/platform selected or entered at clock-in.

## 2026-08-04 - Obsidian Project Notes

- Use the existing project folder as the Obsidian vault so VS Code and Obsidian share the same Markdown files.
- Keep shared notes under `docs/ai`.
- Keep Obsidian workspace layout out of git by ignoring `.obsidian/workspace.json`.
- Use concise feature entries instead of duplicating the entire codebase in notes.

## 2026-08-04 - Model Status Language

- Treat `Fully onboarded` as readiness/completion: real onboarding completion or manual admin mark.
- Treat `Active model` as a separate working status for models who are fully onboarded and currently working.
- Treat `Inactive model` as a separate working status for models who are no longer working or did not continue.
- Track approved websites separately from onboarding completion and working status.
