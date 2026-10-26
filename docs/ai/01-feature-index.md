# Feature Index

Last updated: 2026-10-04

Use this file as the first stop before changing a feature. Read the matching entry, inspect the "Start here" files first, then expand only to the related files named by the code.

## Public Marketing Site And SEO

Doll After Dark: `/doll-after-dark`, `resources/views/marketing/doll-after-dark.blade.php`,
`resources/css/doll-after-dark.css`, and client-supplied `public/images/doll-after-dark/`.
Uses shared navigation; agency CTA links to the existing application and the course is not purchasable.
Site Editor fields/defaults live in `MarketingContent::dollAfterDarkPage()`; the public template reads the shared marketing helpers. Regression coverage: `tests/Feature/DollAfterDarkEditorTest.php`.

Start here:

- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/TestimonialController.php`
- `resources/views/home.blade.php`
- `resources/views/marketing`
- `resources/views/components/layouts/marketing.blade.php`
- `resources/views/components/marketing-navbar.blade.php`
- `resources/views/components/marketing-footer.blade.php`
- `routes/web.php`

Related:

- `app/Support/MarketingContent.php`
- `app/Support/marketing_helpers.php`
- `resources/views/partials/google-tag-manager-head.blade.php`
- `resources/views/partials/google-tag-manager-body.blade.php`
- `resources/views/partials/google-analytics.blade.php`
- `docs/google-translation-cost-explanation.md`

Notes: Handles homepage, public story/lifestyle pages, success stories, SEO landing pages, the public Guides hub and beginner topic cluster, navigation/footer, structured data, GTM/GA partials, sitemap/domain context, and `.net` to `.com` marketing migration.

Withdrawal pending deployment (2026-10-04): eight SEO/guide URLs are disabled in the review branch; templates remain for private review. See `tests/Feature/MarketingWithdrawalTest.php` and the current-state note. Client/head-office approval is required before restoration.

## Applications And Applicant Review

Start here:

- `app/Http/Controllers/ApplyController.php`
- `app/Http/Controllers/Admin/AdminApplicationController.php`
- `app/Models/ModelApplication.php`
- `resources/views/apply.blade.php`
- `resources/views/admin/applications/index.blade.php`
- `resources/views/components/application-terms.blade.php`
- `routes/web.php`

Related:

- `app/Mail/ApplicationSubmittedMail.php`
- `app/Mail/MemberApplicationApprovedMail.php`
- `app/Mail/AccountApprovalMail.php`
- `app/Services/ModelEmailSyncService.php`
- `app/Services/ModelRecordDeletionService.php`
- `database/migrations/*model_applications*`
- `database/migrations/2026_07_03_000000_add_terms_acceptance_to_model_applications_table.php`

Notes: Handles public model applications, photo uploads, terms acceptance, admin review, applicant email correction, approval/rejection, approval email resend, and delete cleanup.

## Referrals

Start here:

- `app/Http/Controllers/Member/MemberReferralController.php`
- `app/Http/Controllers/Admin/AdminReferralController.php`
- `app/Http/Controllers/Admin/AdminApplicationController.php`
- `app/Models/ModelReferral.php`
- `resources/views/member/referrals/index.blade.php`
- `resources/views/admin/referrals/index.blade.php`
- `routes/web.php`

Related:

- `database/migrations/2026_05_14_030000_add_referral_support.php`
- `database/migrations/2026_05_18_000000_add_status_application_index_to_model_referrals.php`
- `app/Services/ModelRecordDeletionService.php`

Notes: Handles member referral submission, admin referral lists, referral conversion into applications, rejection, reward-paid status, and referral photo viewing.

## Member Onboarding And Verification

Start here:

- `app/Http/Controllers/Member/MemberOnboardingController.php`
- `app/Http/Controllers/Member/MemberVerificationController.php`
- `app/Http/Controllers/Admin/AdminOnboardingController.php`
- `app/Models/ModelProfile.php`
- `app/Support/OnboardingFormDefinition.php`
- `resources/views/member/onboarding/edit.blade.php`
- `resources/views/member/verification/edit.blade.php`
- `resources/views/admin/onboarding`
- `routes/web.php`

Related:

- `app/Mail/ModelInformationSubmittedMail.php`
- `app/Mail/VerificationRequestMail.php`
- `app/Mail/VerificationResubmissionMail.php`
- `app/Mail/VerificationSubmissionReceivedMail.php`
- `app/Mail/CommunityAccessMail.php`
- `database/migrations/*model_profiles*`
- `database/migrations/2026_07_05_000000_add_custom_onboarding_answers_to_model_profiles.php`
- `database/migrations/2026_07_07_000000_add_encrypted_payout_details_and_refresh_onboarding_options.php`
- `database/migrations/2026_07_19_000000_add_manual_fully_onboarded_to_model_profiles.php`
- `database/migrations/2026_08_04_000000_add_work_status_and_approved_platforms_to_model_profiles_table.php`

Notes: Handles onboarding form fields, verification documents, admin verification decisions, verification instructions, community invite state, manual fully onboarded status, approved platforms, and working/inactive status.

Watch points:

- Fully onboarded and active model are intentionally different states.
- Payout details are encrypted and should not be exposed in docs or logs.

## Model Progress And Admin Model Accounts

Start here:

- `app/Http/Controllers/Admin/AdminModelProgressController.php`
- `app/Http/Controllers/Admin/AdminWeeklyModelDrawController.php`
- `app/Models/User.php`
- `app/Models/ModelProfile.php`
- `app/Models/WeeklyModelDraw.php`
- `app/Models/WeeklyModelDrawEntry.php`
- `app/Models/WeeklyModelDrawPrize.php`
- `resources/views/admin/models-progress.blade.php`
- `resources/views/admin/weekly-model-draws`
- `routes/web.php`

Related:

- `app/Services/ModelRecordDeletionService.php`
- `app/Services/UserSessionService.php`
- `database/migrations/2026_05_31_000000_add_last_login_at_to_users_table.php`
- `database/migrations/2026_08_03_000000_add_auth_session_version_to_users_table.php`
- `database/migrations/2026_08_17_000000_create_weekly_model_draw_tables.php`

Notes: Handles admin model progress overview, login access changes, generated passwords, model deletion, last-login context, session invalidation, and admin-only weekly model draw preparation/history.

Watch points:

- Weekly model draws are admin-only records. Do not expose draw prep, earnings import, prizes, or winner history in model/member workflows unless Kayla explicitly requests that later.
- Live spins happen outside the app in OBS using Wheel of Names; the app exports qualified names and records the result/recording link.

## Courses, Academy, Lessons, And Access Requests

Start here:

- `app/Http/Controllers/Admin/AdminCourseController.php`
- `app/Http/Controllers/Admin/AdminModuleController.php`
- `app/Http/Controllers/Admin/AdminLessonController.php`
- `app/Http/Controllers/Member/MemberCourseController.php`
- `app/Http/Controllers/Member/LessonProgressController.php`
- `app/Models/Course.php`
- `app/Models/CourseModule.php`
- `app/Models/Lesson.php`
- `app/Models/LessonContentBlock.php`
- `app/Models/LessonProgress.php`
- `resources/views/admin/courses`
- `resources/views/member/courses`
- `routes/web.php`

Related:

- `app/Models/CourseAccessRequest.php`
- `app/Models/CourseAccessRequestFile.php`
- `app/Models/CourseEnrollment.php`
- `app/Observers/CourseEnrollmentObserver.php`
- `app/Services/CourseCommunityService.php`
- `app/Http/Controllers/Member/CourseAssetController.php`
- `app/Http/Controllers/Member/CourseChatController.php`
- `app/Http/Controllers/Admin/Concerns/ManagesLessonContentBlocks.php`
- `resources/js/admin-course-form.js`
- `resources/js/pdf-viewer.js`
- `database/migrations/*course*`
- `database/migrations/*lesson*`

Notes: Handles course CRUD, modules, lessons, lesson content blocks, lesson progress, course community access, access requests/proofs, private academy files, previews, visibility, ordering, accent colors, gated course assets, and manual chatter training unlocks through course enrollments.

Watch points:

- Private course files are routed through controllers; do not expose storage paths directly.
- Route changes should be checked with route cache where possible.

## Bunny Video And Academy Files

Start here:

- `app/Http/Controllers/Admin/BunnyVideoController.php`
- `app/Http/Controllers/Admin/AdminAcademyFileController.php`
- `app/Services/BunnyStreamClient.php`
- `resources/views/admin/courses/partials/bunny-video-modal.blade.php`
- `resources/views/admin/courses/partials/course-outline-upload.blade.php`
- `config/services.php`
- `.env.example`

Related:

- `app/Http/Controllers/Admin/AdminCourseController.php`
- `app/Http/Controllers/Admin/AdminLessonController.php`
- `app/Http/Controllers/Member/CourseAssetController.php`
- `database/migrations/2026_05_07_020000_add_bunny_video_fields_to_lessons_table.php`
- `database/migrations/2026_05_08_010000_add_bunny_intro_video_fields_to_courses_table.php`

Notes: Handles Bunny Stream API integration, upload intents, video metadata, course intro videos, lesson videos, and protected academy file delivery.

## Community Chat

Start here:

- `app/Http/Controllers/Community/CommunityController.php`
- `app/Http/Controllers/Community/CommunityChannelController.php`
- `app/Http/Controllers/Community/CommunityMessageController.php`
- `app/Http/Controllers/Community/CommunityPresenceController.php`
- `app/Http/Controllers/Community/CommunityModerationController.php`
- `app/Http/Controllers/Community/MessageReactionController.php`
- `app/Models/CommunityChannel.php`
- `app/Models/CommunityMessage.php`
- `resources/views/community/show.blade.php`
- `resources/js/community.js`
- `routes/web.php`
- `routes/channels.php`

Related:

- `app/Events/CommunityMessageCreated.php`
- `app/Events/CommunityMessageDeleted.php`
- `app/Events/CommunityReactionUpdated.php`
- `app/Models/CommunityChannelAccess.php`
- `app/Models/CommunityMemberTimeout.php`
- `app/Models/CommunityMessageRead.php`
- `app/Models/CommunityModerationLog.php`
- `app/Models/MessageReaction.php`
- `app/Support/CommunityModeration.php`
- `app/Support/CommunityPresence.php`
- `app/Support/DefaultCommunityChannels.php`
- `app/Http/Middleware/EnsureCommunityAccessIsAssigned.php`
- `app/Http/Middleware/LogCommunityRequestPerformance.php`
- `config/community.php`
- `config/broadcasting.php`
- `config/reverb.php`
- `resources/js/echo.js`
- `database/migrations/*community*`
- `database/migrations/*message_reactions*`

Notes: Handles channels, messages, attachments, read state, reactions, pin/delete, moderation timeouts, presence, typing indicators, private broadcasting, Reverb/Echo integration, and community access checks.

Watch points:

- Community performance indexes matter; avoid unbounded message queries.
- Attachments should remain protected behind controller access checks.

## Email Campaigns And Marketing Preferences

Start here:

- `app/Http/Controllers/Admin/AdminEmailCampaignController.php`
- `app/Http/Controllers/MarketingPreferenceController.php`
- `app/Services/EmailCampaignDispatcher.php`
- `app/Jobs/SendEmailCampaignDelivery.php`
- `app/Mail/MarketingCampaignMail.php`
- `app/Models/EmailCampaign.php`
- `app/Models/EmailCampaignRun.php`
- `app/Models/EmailCampaignDelivery.php`
- `resources/views/admin/email-campaigns`
- `resources/views/emails/marketing-campaign.blade.php`
- `resources/views/marketing/email-preferences.blade.php`
- `routes/web.php`

Related:

- `app/Models/User.php`
- `app/Models/ModelApplication.php`
- `app/Models/ModelProfile.php`
- `database/migrations/2026_07_07_040000_create_email_campaigns.php`
- `routes/console.php`
- `docs/admin-website-and-email-guide.md`

Notes: Handles audience selection, immediate sends, scheduled sends, recurring sends, campaign summaries, delivery tracking, unsubscribe links, and marketing preference updates.

Watch points:

- Raw applicant delivery currently depends on a real `User` for safe unsubscribe handling.
- Queue and scheduler behavior may be involved.

## Chatter Applications, Accounts, Time Tracking, And Payroll

Start here:

- `app/Http/Controllers/Chatter/ChatterApplicationController.php`
- `app/Http/Controllers/Chatter/ChatterDashboardController.php`
- `app/Http/Controllers/Chatter/ChatterClockController.php`
- `app/Http/Controllers/Chatter/ChatterTimesheetController.php`
- `app/Http/Controllers/Admin/AdminChatterHoursController.php`
- `app/Http/Controllers/Admin/AdminChatterHoursExportController.php`
- `app/Models/ChatterProfile.php`
- `app/Models/ChatterShift.php`
- `app/Models/ChatterBreak.php`
- `app/Models/ChatterTimesheet.php`
- `resources/views/chatter`
- `resources/views/admin/chatter-hours`
- `resources/js/chatter-time-tracker.js`
- `routes/web.php`
- `routes/console.php`

Related:

- `app/Models/ChatterRequest.php`
- `app/Models/ChatterPayAdjustment.php`
- `app/Models/ChatterPayRate.php`
- `app/Models/ChatterRoleAssignment.php`
- `app/Models/ChatterWorkRole.php`
- `app/Models/ChatterTimeAudit.php`
- `app/Models/ChatterModelAssignment.php`
- `app/Models/CourseEnrollment.php`
- `app/Services/ChatterAccountService.php`
- `app/Services/ChatterClockService.php`
- `app/Services/ChatterPayrollService.php`
- `app/Services/UsdPhpExchangeRateService.php`
- `app/Support/ChatterCurrency.php`
- `app/Support/DesignedXlsxWorkbook.php`
- `app/Jobs/RefreshChatterPayrollForShift.php`
- `app/Mail/ChatterInvitationMail.php`
- `app/Mail/ChatterWorkflowMail.php`
- `database/migrations/*chatter*`

Notes: Handles chatter applications, invitations, chatter accounts, account employment status, live open-shift working status, model assignments, role/rate pairs, platform/model-at-clock-in tracking, starting/ending token or GBP balances, generated earning commission, manually unlocked academy training courses, breaks, automatic weekly timesheet readiness, audit-only problem reports, admin approval, pay adjustments, fixed-rate payroll summaries, attendance, XLSX export, and USD/PHP exchange handling.

Watch points:

- Break time must never count toward worked hours.
- Payroll weeks use Europe/London time, with reporting segments clipped at the Sunday 11:59 PM / Monday 12:00 AM UK cutoff while preserving the single real shift.
- Payroll uses the role/rate frozen on each shift; overtime, premium rates, and night windows are no longer active payroll inputs.
- Chatter clock-in model choices must come from active chatter/model assignments; payroll snapshots preserve the selected model.
- Chatter clock-in requires a starting token balance and clock-out requires an ending token balance; generated tokens pay 3% commission using the configured token USD value, and missing legacy token balances pay no commission.
- Admin attendance/payroll views and XLSX export default to the current Europe/London payroll week when no date filter is selected; older weeks require explicit filters.
- Balance in/out stays per shift; weekly tables show generated earning and commission totals instead of a misleading single weekly balance.
- Chatter account employment status is separate from live working status; live admin lists must use open shifts where `clocked_out_at` is null.
- Chatter timesheets do not require chatter submission; completed weeks become Ready for review automatically, and weekly model reviews are enforced when admin approves payroll.
- Chatter problem reports notify admin and create an audit record without reopening or editing payroll.
- Zero-work payroll periods and zero-minute shifts remain stored but are omitted from Admin Chatter Hours lists and Excel exports; adjustment/payable rows remain visible and direct timesheet review still works.
- Clock/state endpoints use throttling and request blocking to avoid duplicate shift events.

## Chatter And Model Review Workflows

Start here:

- `app/Http/Controllers/Admin/AdminChatterPerformanceReviewController.php`
- `app/Http/Controllers/Admin/AdminChatterModelReviewController.php`
- `app/Http/Controllers/Member/MemberChatterPerformanceReviewController.php`
- `app/Http/Controllers/Chatter/ChatterModelReviewController.php`
- `app/Models/ChatterPerformanceReview.php`
- `app/Models/ChatterModelReview.php`
- `resources/views/admin/chatter-reviews`
- `resources/views/admin/chatter-model-reviews`
- `resources/views/member/chatter-reviews`
- `resources/views/chatter/model-reviews`
- `routes/web.php`

Related:

- `app/Models/ChatterModelAssignment.php`
- `database/migrations/2026_07_28_000000_create_chatter_performance_reviews_table.php`
- `database/migrations/2026_07_28_010000_create_chatter_model_reviews_table.php`
- `database/migrations/2026_07_29_000000_add_review_tracking_to_chatter_model_reviews_table.php`
- `database/migrations/2026_07_29_000000_create_chatter_model_assignments_table.php`

Notes: Handles private model-to-chatter reviews, chatter-to-model reviews, signed review links, weekly review expectations, admin review status, and confidential review browsing.

Watch points:

- These reviews are private admin/member/chatter workflows, not public testimonials.

## Testimonials And Success Stories

Start here:

- `app/Http/Controllers/TestimonialController.php`
- `app/Http/Controllers/Member/MemberTestimonialController.php`
- `app/Http/Controllers/Admin/AdminTestimonialController.php`
- `app/Models/Testimonial.php`
- `resources/views/marketing/success-stories.blade.php`
- `resources/views/member/testimonials/create.blade.php`
- `resources/views/admin/testimonials`
- `routes/web.php`

Related:

- `database/migrations/*testimonials*`
- `database/migrations/2026_07_07_030000_remove_demo_testimonials.php`

Notes: Handles public success stories, member testimonial submission, admin CRUD, approval, visibility, image uploads, display handles, and testimonial ordering/display.

## Notifications

Start here:

- `app/Http/Controllers/NotificationController.php`
- `app/Notifications/SystemNotification.php`
- `resources/views/notifications/index.blade.php`
- `resources/views/layouts/partials/notification-bell.blade.php`
- `routes/web.php`

Related:

- `database/migrations/2026_05_18_000000_create_notifications_table.php`
- `database/migrations/2026_05_18_040000_add_recent_notifications_index.php`
- `app/Services/AdminActivityNotifier.php`
- `app/Mail/AdminActivityAlertMail.php`

Notes: Handles in-app notifications, notification opening, mark-all-read, notification bell display, and admin activity alert emails.

## Auth, Profile, Roles, And Session Policy

Start here:

- `routes/auth.php`
- `app/Http/Controllers/Auth`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/ProfilePhotoController.php`
- `app/Models/User.php`
- `app/Services/UserSessionService.php`
- `resources/views/auth`
- `resources/views/profile`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/layouts/partials/member-account-menu.blade.php`

Related:

- `app/Http/Middleware/EnsureUserIsAdmin.php`
- `app/Http/Middleware/EnsureUserIsModel.php`
- `app/Http/Middleware/EnsureUserIsChatter.php`
- `app/Http/Middleware/EnforceAuthenticatedSessionPolicy.php`
- `app/Http/Middleware/SecurityHeaders.php`
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/2026_05_06_120000_add_role_to_users_table.php`
- `database/migrations/2026_05_12_010000_add_profile_photo_path_to_users_table.php`
- `database/migrations/2026_08_03_000000_add_auth_session_version_to_users_table.php`

Notes: Handles login, registration, password reset, verification prompts, profile updates, profile photos, role-based routing, account deletion, session versioning, and security headers.

## Admin Dashboard, Settings, And Site Editor

Start here:

- `app/Http/Controllers/Admin/AdminDashboardController.php`
- `app/Http/Controllers/Admin/AdminSettingsController.php`
- `app/Models/SiteSetting.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/site-editor/edit.blade.php`
- `resources/views/layouts/admin.blade.php`
- `routes/web.php`

Related:

- `app/Support/MarketingContent.php`
- `config/paradise.php`
- `database/migrations/2026_05_20_100000_create_site_settings_table.php`

Notes: Handles admin dashboard totals/activity, theme settings, marketing content editing, and site-wide configurable content.

Sidebar unseen badges: `app/Services/AdminSidebarBadges.php`, `app/Http/Middleware/EnsureUserIsAdmin.php`, `tests/Feature/AdminSidebarBadgesTest.php`, and the `admin_sidebar_visits` migration. List visits acknowledge a section per administrator; pending page totals are separate.

Two draft marketing pages reuse Site Editor fields and existing site_settings storage. See `app/Http/Controllers/EditablePageController.php`, `resources/views/marketing/editable-page.blade.php`, and `tests/Feature/EditablePagesTest.php` for private previews, protected uploads, and publishing checks.

## Translation And Localization

Start here:

- `app/Http/Controllers/TranslationController.php`
- `app/Services/TranslationService.php`
- `resources/js/translator.js`
- `resources/views/components/language-selector.blade.php`
- `config/services.php`
- `.env.example`
- `routes/web.php`

Related:

- `docs/google-translation-cost-explanation.md`
- `resources/views/components/layouts/marketing.blade.php`
- `resources/views/components/marketing-navbar.blade.php`
- `resources/views/components/marketing-footer.blade.php`

Notes: Handles Google Cloud Translation API integration, cached translations, language selector UI, flags/search, translation throttling, and public-page-only translation behavior.

## Exports, Reports, And Admin Spreadsheets

Start here:

- `app/Http/Controllers/Admin/AdminCrmExportController.php`
- `app/Http/Controllers/Admin/AdminChatterHoursExportController.php`
- `app/Support/DesignedXlsxWorkbook.php`
- `routes/web.php`

Related:

- `app/Models/ModelApplication.php`
- `app/Models/ModelProfile.php`
- `app/Models/ChatterTimesheet.php`
- `app/Services/ChatterPayrollService.php`
- `app/Support/SqlDumpInsertParser.php`
- `docs/admin-website-and-email-guide.md`

Notes: Handles applications export, onboarding export, individual profile export, chatter hours export, designed XLSX output, and CRM/admin handoff reports.

## Frontend Assets And Build Pipeline

Start here:

- `resources/css/app.css`
- `resources/js/app.js`
- `resources/js/bootstrap.js`
- `resources/js/admin-course-form.js`
- `resources/js/application-form.js`
- `resources/js/chatter-time-tracker.js`
- `resources/js/community.js`
- `resources/js/echo.js`
- `resources/js/pdf-viewer.js`
- `resources/js/translator.js`
- `vite.config.js`
- `tailwind.config.js`
- `package.json`

Related:

- `resources/views/layouts`
- `resources/views/components`
- `postcss.config.js`

Notes: Handles shared CSS, Vite entrypoints, Tailwind config, Echo/Reverb setup, and feature-specific frontend behavior.

Watch points:

- Run `npm run build` after Blade/CSS/JS changes that affect compiled assets.

## Deployment, Environment, And Operations

Start here:

- `docs/vps-deployment-commands.md`
- `docs/project-handover-and-technical-overview.md`
- `.env.example`
- `config/app.php`
- `config/cache.php`
- `config/database.php`
- `config/filesystems.php`
- `config/mail.php`
- `config/queue.php`
- `config/reverb.php`
- `routes/console.php`

Related:

- `composer.json`
- `package.json`
- `phpunit.xml`
- `.github/workflows/ci.yml`
- `deployment`

Notes: Handles Hostinger VPS deployment, environment variables, queues, scheduler, Reverb, cache/config/route/view optimization, CI, and operational runbooks.

Watch points:

- Use `php artisan migrate --force` only for production deployment instructions, not local guessing.
- Only run `composer install --no-dev --optimize-autoloader` when Composer files changed.
- Only run `npm ci` when package files changed.

## AI Project Memory

Start here:

- `AGENTS.md`
- `CODEX.md`
- `docs/ai/README.md`
- `docs/ai/00-ai-project-map.md`
- `docs/ai/01-feature-index.md`
- `docs/ai/02-current-state.md`
- `docs/ai/03-decisions-log.md`
- `docs/ai/04-change-log.md`
- `docs/ai/05-ai-rules.md`

Related:

- `.obsidian`
- `.gitignore`

Notes: Handles Obsidian/KOS-style project memory, AI context routing, current state, durable decisions, and compact post-change summaries.
