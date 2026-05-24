# Paradise Dolls Project Handover And Technical Overview

Last updated: 2026-05-24

## Purpose

This document is the main handover reference for the Paradise Dolls web application. It explains what the project is, how it is hosted, what services it depends on, what admins can edit, how course content is managed, and what a new developer or project owner needs before taking over maintenance.

Do not store passwords, API keys, private SSH keys, database dumps, or production `.env` values in this document or in Git.

## Project Summary

Paradise Dolls is a Laravel web application for:

- A public marketing website.
- A model application and onboarding workflow.
- Admin-managed model verification.
- Course and training access.
- Course-specific approval and proof upload requests.
- Member notifications.
- Community chat and course community channels.
- Admin-editable public website content.

The project is a web application / platform, not a static website. It includes a database, user accounts, admin dashboards, file uploads, video integrations, email sending, queues, and real-time chat.

## Current Production Environment

- Live domain: `https://getrichwithparadisedolls.net`
- Hosting: Hostinger KVM VPS
- Server OS: Ubuntu 24.04 LTS
- Web server: Nginx
- PHP runtime: PHP 8.3 FPM
- Database: MySQL 8
- Cache/session support: Redis
- Queue worker: Laravel database queue through Supervisor
- WebSocket server: Laravel Reverb through Supervisor
- SSL: Let's Encrypt / Certbot
- Deployment path: `/var/www/paradisedollz`

## Tech Stack

### Backend

- PHP `^8.2`
- Laravel `^12.0`
- Laravel Breeze for authentication scaffolding
- Laravel Reverb for real-time WebSockets
- Laravel queues using the database queue driver
- MySQL for primary application data
- Redis for cache and community presence performance
- Laravel notifications, mailables, policies/middleware, migrations, and seeders

### Frontend

- Blade templates
- Tailwind CSS
- Alpine.js
- Vite
- Laravel Echo
- Pusher JS client, used with Laravel Reverb protocol
- PDF.js for PDF lesson rendering

### Important Composer Packages

- `laravel/framework`
- `laravel/reverb`
- `laravel/tinker`
- `resend/resend-php`
- Dev/test tools: PHPUnit, Laravel Pint, Laravel Sail, Laravel Pail, Collision, Faker, Mockery

### Important NPM Packages

- `vite`
- `laravel-vite-plugin`
- `tailwindcss`
- `@tailwindcss/forms`
- `alpinejs`
- `axios`
- `laravel-echo`
- `pusher-js`
- `pdfjs-dist`

## Integrated Providers And Services

These are external services or server services the app depends on.

### Hostinger VPS

Hostinger hosts the server that runs Laravel, Nginx, MySQL, Redis, Supervisor, and Reverb.

The client should receive:

- Hostinger account ownership or owner-level access.
- VPS management access.
- Domain/DNS management access.
- Billing ownership or billing transfer.

### Domain And DNS

The domain should point to the VPS IP with DNS records similar to:

- `A @ -> VPS IPv4`
- `A www -> VPS IPv4`

Email provider DNS records are also required for Resend or any future email provider.

### Bunny Stream / Bunny CDN

Bunny is used for course video hosting and playback.

Environment keys:

```env
BUNNY_LIBRARY_ID=
BUNNY_API_KEY=
BUNNY_CDN_HOSTNAME=
BUNNY_UPLOAD_SIGNATURE_TTL=86400
BUNNY_CONNECT_TIMEOUT=10
BUNNY_TIMEOUT=30
```

Important notes:

- Videos are not stored in the Git repository.
- Videos are uploaded/selected from the admin course editor.
- Existing saved videos may reference the Bunny library ID used at upload time.
- If the Bunny account changes, update the `.env` keys and run Laravel config cache commands.
- The client should receive Bunny account owner access or the videos should be transferred to the client's Bunny account.

### Resend

Resend is used for outbound application, verification, approval, notification, and course access emails.

Environment keys:

```env
MAIL_MAILER=resend
RESEND_API_KEY=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
```

Important notes:

- The sending domain must be verified in Resend DNS.
- The client should receive Resend account ownership or a new API key should be issued after handover.
- If Resend is replaced with SMTP or another provider, update `.env` and test email sending.

Test command:

```bash
php artisan mail:test recipient@example.com
```

### Laravel Reverb

Laravel Reverb powers real-time community chat events.

Production server-side values should point internally to the Reverb process:

```env
BROADCAST_CONNECTION=reverb
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080
```

Browser-facing Vite values should point to the public HTTPS domain:

```env
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=getrichwithparadisedolls.net
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

Important notes:

- Reverb is supervised by Supervisor.
- If community broadcasts fail, check `php artisan queue:failed`, Supervisor logs, and Reverb `.env` values.

### Redis

Redis is used for cache and community presence performance.

Environment keys:

```env
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
REDIS_CACHE_DB=1
```

### MySQL

MySQL stores users, courses, lessons, onboarding data, applications, notifications, proof uploads metadata, community messages, and settings.

Environment keys:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paradisedollz
DB_USERNAME=
DB_PASSWORD=
```

The database itself and backups must be transferred securely during ownership handoff.

### Discord Invite

The app still includes an optional community invite configuration value:

```env
PARADISE_COMMUNITY_URL=
PARADISE_COMMUNITY_ROLE_NAME=
```

The public/community flow now mainly uses the internal community chat and course community access. If Discord is no longer part of the business process, keep this value updated or remove the workflow in a future cleanup.

## Main Application Features

### Public Marketing Website

Public pages:

- Home
- Our Story
- Work From Home
- Work From Paradise
- Perks
- Multistreaming
- Success Stories
- Apply

Admins can edit main site content through:

`Admin > Site Editor`

Editable areas include:

- Public page headings and body copy.
- Buttons and links.
- Hero/section images.
- Navbar/footer text and image content.
- Page-specific lists and cards.

The Site Editor uses structured fields, not a drag-and-drop page builder. This protects the design while still allowing the business team to update content.

### Applications

The public application form allows prospective models to apply.

Admins can:

- View applications.
- Review uploaded application photos.
- Approve or reject applications.
- Convert referrals to applications.
- Trigger approval emails and onboarding access.

### Member Onboarding

Members can complete onboarding/profile information after account creation.

Admin onboarding stages:

- `registration`
- `callback`
- `onboarding`
- `verification`
- `active`

Admins can:

- View model profile details.
- Move members through stages.
- Review verification documents.
- Approve or reject verification.
- Request resubmission.
- Unlock or lock individual courses.
- Review course-specific access proof files.

### Verification Documents

Global model verification supports fixed uploads such as:

- Valid ID
- Selfie holding ID
- Platform codes, where applicable

These are separate from course-specific proof uploads.

### Course Access Proof Uploads

For locked courses, verified models can request access and upload files directly inside the course access request modal.

Supported proof files:

- JPG
- JPEG
- PNG
- WEBP
- PDF

Limits:

- Up to 5 files per submission.
- Maximum 10 MB per file.
- Files are stored privately on the local disk.

Admins review these in:

`Admin > Onboarding > Model Profile > Website Walkthrough Access`

Admins can:

- View the model's access note.
- View/download course-specific proof files.
- Approve and unlock the course.
- Request resubmission.
- Lock the course again if needed.

### Courses And Lessons

Admins can manage:

- Courses
- Modules
- Lessons
- Lesson content blocks
- Course cover images
- Course intro videos
- Lesson videos
- Lesson PDFs/presentations
- Course access requirements
- Website verification process instructions

Course videos are uploaded/selected through Bunny inside the course editor.

Recommended course structure:

1. Course = one platform or topic.
2. Module = learning stage.
3. Lesson = one task/topic.
4. Lesson blocks = overview, presentation video, walkthrough video, steps, tips, resources.

Typical lesson block order:

1. Heading
2. Text overview
3. Presentation video
4. Heading: `Now Follow Along`
5. Walkthrough video
6. Steps
7. Tips

### Existing Course Structure Seeders

The codebase includes course seeders for platform-specific training structures:

- `AdultWorkCourseSeeder`
- `BabestationCourseSeeder`
- `ChaturbateCourseSeeder`
- `StripchatCourseSeeder`

These seed course/module/lesson/block structures into the database. They do not upload real videos.

Run on production only after a database backup:

```bash
cd /var/www/paradisedollz
mysqldump --single-transaction --no-tablespaces -u paradisedollz -p paradisedollz > /root/paradisedollz-before-course-seed-$(date +%F-%H%M).sql
php artisan db:seed --class=ChaturbateCourseSeeder --force
```

Replace `ChaturbateCourseSeeder` with the seeder needed.

Important:

- Seeders create/update course structure.
- Videos still need to be uploaded manually in the admin dashboard.
- Avoid rerunning a course seeder after manually assigning videos unless the developer has confirmed it will not overwrite video blocks.

### Notifications

The app has in-system notifications for admins and members.

Examples:

- Admins receive course access request notifications.
- Members receive approval/unlock/resubmission notifications.
- Notification popup links can deep-link to specific onboarding/course request review context.

### Community Chat

The internal community chat includes:

- Real-time messages.
- Channels.
- Course-linked private channels.
- Invite/access controlled channels.
- Attachments.
- Reactions.
- Message deletion.
- Pinning.
- Presence/online state.
- Typing indicators.
- Moderation controls and timeouts.

Course community access is tied to course unlock/enrollment.

### Testimonials / Success Stories

Members can submit testimonials.

Admins can:

- Review testimonials.
- Approve or reject stories.
- Control visibility.
- Add/edit/remove testimonials.

The public Success Stories page displays approved stories.

### Referrals

Members can submit referrals.

Admins can:

- Review referral records.
- Convert referrals into applications.
- Reject referrals.
- Mark rewards as paid.

### Admin Site Editor

Admins can edit public website content without code:

`Admin > Site Editor`

Content is stored in the `site_settings` table under structured marketing content keys.

## Access Control Summary

User roles include:

- Admin
- Model/member

Admin-only areas are protected by admin middleware.

Member-only areas are protected by auth, email verification, and model middleware.

Course lessons, progress, course chat, course resources, and course community access require course enrollment/unlock.

Community chat access requires community access assignment. Admins and moderators may bypass some member gates depending on middleware rules.

## File Storage

Public files:

- Marketing images
- Course cover images
- Other intentionally public assets

Private files:

- Verification documents
- Course access proof uploads
- Community attachments after privacy migration
- Academy/course gated files where applicable

Laravel storage link:

```bash
php artisan storage:link
```

Production upload limits should support course proof files:

- Nginx: `client_max_body_size 64M`
- PHP-FPM: `upload_max_filesize = 10M`
- PHP-FPM: `post_max_size = 64M`

## Local Development Setup

Required local tools:

- PHP 8.2 or newer
- Composer
- Node.js 22
- MySQL
- Optional Redis for closer production parity

Typical setup:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Local development server:

```bash
composer run dev
```

Or run processes manually:

```bash
php artisan serve
npm run dev
php artisan queue:listen
php artisan reverb:start
```

Do not commit local `.env` values.

## Production Deployment

Manual Hostinger deployment:

```bash
cd /var/www/paradisedollz
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
bash deployment/deploy.sh
php artisan migrate:status
php artisan queue:failed
supervisorctl status
```

`deployment/deploy.sh` handles:

- Maintenance mode.
- Cache clearing.
- Migrations.
- Storage link.
- Config, route, view, and event caching.
- Queue restart.
- Supervisor process restart.
- Nginx/PHP-FPM reload.
- Bringing the app back online.

GitHub Actions deployment also exists in:

`.github/workflows/deploy.yml`

It deploys on pushes to `main` when all GitHub deployment secrets are configured.

## Health Check Commands

Run on the VPS:

```bash
cd /var/www/paradisedollz
curl -I https://getrichwithparadisedolls.net
php artisan queue:failed
supervisorctl status
php artisan migrate:status
```

Expected:

- `curl` returns `HTTP/2 200`.
- `php artisan queue:failed` returns no failed jobs.
- Supervisor shows queue and Reverb as `RUNNING`.
- Migrations show expected latest migrations as `Ran`.

## Backups

Before major changes, create a database backup:

```bash
mysqldump --single-transaction --no-tablespaces -u paradisedollz -p paradisedollz > /root/paradisedollz-backup-$(date +%F-%H%M).sql
```

The client should receive:

- A recent database backup.
- Clear restore instructions.
- Confirmation of where file uploads are stored.
- Confirmation of whether uploaded private files are included in any server backup routine.

Recommended ongoing backups:

- Daily VPS backup through Hostinger or another backup provider.
- Periodic off-server database backups.
- Periodic off-server storage backups for uploaded documents, course files, and private proofs.

## Environment Variables To Transfer

Transfer these securely through a password manager or encrypted vault, not by email or screenshots.

Core app:

```env
APP_NAME=
APP_ENV=
APP_KEY=
APP_DEBUG=
APP_URL=
```

Database:

```env
DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Session/cache/queue:

```env
SESSION_DRIVER=
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=
CACHE_STORE=
QUEUE_CONNECTION=
REDIS_HOST=
REDIS_PASSWORD=
```

Email:

```env
MAIL_MAILER=
RESEND_API_KEY=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=
```

Reverb:

```env
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=
REVERB_PORT=
REVERB_SCHEME=
REVERB_SERVER_HOST=
REVERB_SERVER_PORT=
VITE_REVERB_APP_KEY=
VITE_REVERB_HOST=
VITE_REVERB_PORT=
VITE_REVERB_SCHEME=
```

Bunny:

```env
BUNNY_LIBRARY_ID=
BUNNY_API_KEY=
BUNNY_CDN_HOSTNAME=
BUNNY_UPLOAD_SIGNATURE_TTL=
```

Business config:

```env
PARADISE_ONBOARDING_EMAIL=
PARADISE_COMMUNITY_URL=
PARADISE_COMMUNITY_ROLE_NAME=
```

## Secure Credentials Handover

Actual passwords, private keys, API keys, recovery codes, and production `.env` values must not be written into this repository or any Git-tracked documentation.

Use a secure password manager or encrypted handover vault such as:

- 1Password
- Bitwarden
- Dashlane
- Proton Pass
- An encrypted ZIP or encrypted document shared through a secure channel only if a password manager is not available

The client or new developer should receive the following credentials separately from the source code:

### Hosting And Server Access

- Hostinger account login.
- Hostinger billing/owner access.
- VPS root password, if root login is still enabled.
- VPS SSH private key, if SSH keys are used.
- VPS SSH username, host/IP address, and port.
- Any deploy user credentials, if a non-root deploy user is created.
- Server recovery codes or two-factor authentication transfer instructions.

Recommended: transfer owner access to Hostinger instead of only sharing a password.

### Domain And DNS Access

- Domain registrar login.
- Hostinger domain/DNS access, if Hostinger manages the domain.
- DNS management permissions.
- Domain renewal/billing ownership.
- Any email/DNS verification records for Resend, Bunny, or future providers.

### GitHub / Source Code Access

- GitHub repository owner/admin access.
- GitHub organization access, if applicable.
- GitHub Actions secrets access.
- Deployment SSH key used by GitHub Actions.
- Branch protection or deployment workflow notes.

Recommended: add the client as repository owner/admin instead of only sharing a developer account.

### Production Application Credentials

- Production `.env` values.
- `APP_KEY`.
- Database username and password.
- Redis password, if configured.
- Session/cache/queue configuration.
- Reverb app key and secret.
- Any production-only configuration values.

### Database And Backup Access

- MySQL root access, if required.
- Application database user credentials.
- Latest database dump location.
- Backup storage provider access.
- Backup schedule and restore procedure.
- Hostinger VPS backup access, if enabled.

### Email Provider Access

- Resend account access.
- Resend API key.
- Verified sending domain access.
- Mail sender address ownership.
- DNS records used for SPF, DKIM, DMARC, and return path.

### Bunny Video Hosting Access

- Bunny account access.
- Bunny Stream library ID.
- Bunny API key.
- Bunny CDN hostname.
- Confirmation that all final course videos are in the correct Bunny library.
- Confirmation that video ownership is under the client's Bunny account.

### Website Admin Accounts

- Primary admin email and password.
- Backup admin account.
- Instructions for changing admin email/password.
- Instructions for disabling old developer/admin access after handover.

### Third-Party Content And Assets

- Source design files.
- Logo/source brand files.
- Course videos.
- Voiceovers.
- Scripts.
- Presentations.
- PDFs.
- Editable/source files.
- Any stock/media licenses, if applicable.

### Post-Handover Security Steps

After ownership is transferred, the client or new developer should:

- Change the VPS root password.
- Rotate SSH keys if shared during development.
- Rotate `RESEND_API_KEY`.
- Rotate `BUNNY_API_KEY`.
- Rotate `REVERB_APP_SECRET`.
- Regenerate deployment keys if GitHub Actions access changes.
- Remove old developer access from Hostinger, GitHub, Resend, Bunny, and the production admin dashboard.
- Confirm that backups still run after credential rotation.
- Run a full production health check.

## Ownership Transfer Checklist

The client should receive or confirm ownership of:

- Hostinger account and VPS.
- Domain and DNS controls.
- GitHub repository owner/admin access.
- Production `.env` secrets through secure transfer.
- MySQL database backup.
- Uploaded file/storage backup.
- Bunny account/library access or transferred videos.
- Resend account access or new production API key.
- Website admin account.
- Documentation for deployment and maintenance.
- Documentation for course/video management.
- Backup and restore procedure.
- Any source files for logos, images, PDFs, scripts, presentations, and video voiceovers.

## Course Materials Handover Checklist

For each course, collect and transfer:

- Final edited videos.
- Presentation videos.
- Walkthrough videos.
- Voiceover audio files.
- Scripts.
- Slide decks/presentations.
- PDFs.
- Editable/source files.
- Cover images and thumbnails.
- Any additional related assets.

Inside the website, confirm:

- All videos are uploaded or selected in Bunny.
- All PDFs open correctly.
- All buttons and links work.
- All interactive tools load correctly.
- System functions work for admin and member roles.
- Course unlock and proof upload flows work.
- Member progress tracking works.
- Course community/chat access works after unlock.

Specific pending client note:

- The AI Voice Course main module video must be added and fully integrated if that course/module is part of the final scope.

## Final Review Checklist

Before final approval/payment handoff, review:

- Public website pages.
- Application form.
- Login and password reset.
- Admin dashboard.
- Site Editor.
- Applications workflow.
- Onboarding workflow.
- Verification upload and review.
- Course access request and proof upload.
- Course unlock/lock/resubmission.
- Course videos and lesson content.
- Course PDFs/resources.
- Member course access and progress.
- Community chat.
- Notifications.
- Email sending.
- Website performance.
- Mobile responsiveness.
- Backups.
- Admin/source code access.

## Notes For Future Developers

- Keep `.env` out of Git.
- Do not expose private verification/proof uploads through public storage.
- Use migrations for schema changes.
- Use seeders only for intentional course structure imports.
- Back up production before running course seeders or destructive migrations.
- Run `php artisan test` before major releases.
- Run `npm run build` after frontend changes.
- Use `bash deployment/deploy.sh` for production deployments.
- Keep Reverb server-side host internal (`127.0.0.1:8080`) and Vite/browser host public (`domain:443`).
- Rotate API keys after ownership transfer if any secrets were shared insecurely.

## Useful File Locations

- Main routes: `routes/web.php`
- Console commands/course SQL importer: `routes/console.php`
- Admin controllers: `app/Http/Controllers/Admin`
- Member controllers: `app/Http/Controllers/Member`
- Community controllers: `app/Http/Controllers/Community`
- Course seeders: `database/seeders`
- Public marketing views: `resources/views/marketing`
- Admin views: `resources/views/admin`
- Member course views: `resources/views/member/courses`
- Deployment scripts: `deployment`
- GitHub Actions deployment: `.github/workflows/deploy.yml`
- Existing private room assessment: `docs/client-private-rooms-and-ownership-assessment.md`
