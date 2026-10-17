# Boss Doll Creators Project Map

## Purpose

Boss Doll Creators is a Laravel application for a public marketing site, model applications, member onboarding, course access, community features, admin operations, email campaigns, chatter time tracking, and confidential review workflows.

## Core Stack

- Backend: Laravel, Blade, Eloquent, queued jobs, scheduled commands.
- Frontend: Blade templates with Vite-built CSS/JavaScript.
- Database: MySQL in production, migrations in `database/migrations`.
- Deployment: Hostinger VPS at `/var/www/paradisedollz`, served by Nginx and PHP-FPM.
- Background services: Laravel queue workers, scheduler, and Reverb managed by Supervisor.

## Important Directories

- `app/Models` - Eloquent models and feature records.
- `app/Http/Controllers` - public, member, admin, and chatter controllers.
- `app/Services` - shared business logic such as translation, deletion, email, payroll, and time calculations.
- `app/Mail` and `app/Jobs` - transactional and campaign email delivery.
- `resources/views` - Blade pages, layouts, components, and emails.
- `resources/js` and `resources/css` - frontend behavior and styles compiled by Vite.
- `routes/web.php` - primary web routes.
- `database/migrations` - production database structure changes.
- `docs` - deployment, cost, handover, and client-facing documentation.
- `docs/ai` - compact project memory for AI-assisted work.

## Main Feature Areas

- Marketing website and translation selector.
- Model application form, terms acceptance, and application admin review.
- Member onboarding, verification, approved websites, fully onboarded/manual status, and model work status.
- Course library, course access requests, progress, and admin course management.
- Testimonials/success stories and review notifications.
- Email campaigns with immediate, scheduled, and recurring delivery.
- Community chat, Discord/WhatsApp community links, and member notifications.
- Chatter accounts, time tracker, roles/rates, attendance, payroll export, and chatter/model reviews.
- Admin analytics, Google Tag Manager, Google Analytics, sitemap, and SEO/domain setup.

## Deployment Checklist

Use [VPS Deployment Commands](../vps-deployment-commands.md) for the full guide.

Common production deploy:

```bash
cd /var/www/paradisedollz
git status --short
git pull origin main
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Only run `composer install --no-dev --optimize-autoloader` when `composer.json` or `composer.lock` changed.

Only run `npm ci` when `package.json` or `package-lock.json` changed.
