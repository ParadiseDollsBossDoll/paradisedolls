# VPS Deployment Commands

Use this guide after pushing changes to GitHub and deploying them to the Hostinger VPS.

## Usual Deploy Commands

Run these on the VPS:

```bash
cd /var/www/paradisedollz

git status --short
git pull origin main

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## If CSS Or JavaScript Changed

Run this when files such as `resources/css/app.css`, `resources/js/...`, or frontend Blade styling changed:

```bash
npm run build
```

For most UI fixes, including style/layout fixes, run:

```bash
cd /var/www/paradisedollz

git status --short
git pull origin main
npm run build

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## If PHP Dependencies Changed

Run this when `composer.json` or `composer.lock` changed:

```bash
composer install --no-dev --optimize-autoloader
```

This installs or updates Laravel/PHP dependencies for production.

## If Frontend Dependencies Changed

Run this when `package.json` or `package-lock.json` changed:

```bash
npm ci
npm run build
```

`npm ci` installs the exact frontend dependencies from the lock file.

`npm run build` rebuilds the production CSS and JavaScript files.

## If Database Migrations Changed

Run this when files in `database/migrations` changed:

```bash
php artisan migrate --force
```

This updates the production database structure.

Use `--force` on production because Laravel requires confirmation before running migrations on a live server.

## If The `.env` File Changed

Run this after editing environment variables on the VPS:

```bash
php artisan optimize:clear
php artisan config:cache
```

Laravel caches environment/config values in production, so these commands make sure the new `.env` values are used.

## What Each Command Does

```bash
git status --short
```

Checks whether the VPS has local uncommitted changes.

If this shows files, pause before pulling because local VPS changes may conflict with GitHub changes.

```bash
git pull origin main
```

Downloads the latest pushed code from GitHub into the VPS.

```bash
composer install --no-dev --optimize-autoloader
```

Installs PHP dependencies for production.

Run this when `composer.json` or `composer.lock` changed, or after a fresh deploy.

```bash
npm ci
```

Installs frontend dependencies exactly from `package-lock.json`.

Run this when `package.json` or `package-lock.json` changed, or after a fresh deploy.

```bash
npm run build
```

Builds production CSS and JavaScript into `public/build`.

Run this when CSS, JavaScript, or frontend styling changed.

```bash
php artisan migrate --force
```

Applies database changes.

Run this when migration files changed.

```bash
php artisan optimize:clear
```

Clears Laravel's cached config, routes, views, and optimized files.

Good to run after most deploys.

```bash
php artisan config:cache
```

Rebuilds Laravel's config cache for production.

Run this after pulling code or changing `.env`.

```bash
php artisan route:cache
```

Rebuilds Laravel's route cache for production.

Run this after route changes or as part of the usual deploy routine.

```bash
php artisan view:cache
```

Precompiles Blade views.

Run this after Blade/template changes or as part of the usual deploy routine.

## Quick Decision Guide

Always run:

```bash
git pull origin main
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If CSS/JS changed:

```bash
npm run build
```

If `composer.json` or `composer.lock` changed:

```bash
composer install --no-dev --optimize-autoloader
```

If `package.json` or `package-lock.json` changed:

```bash
npm ci
npm run build
```

If `database/migrations` changed:

```bash
php artisan migrate --force
```

If `.env` changed:

```bash
php artisan optimize:clear
php artisan config:cache
```

## For The Recent Application List And UI Visibility Fixes

For the recent platform/application list update and onboarding visibility fix, run:

```bash
cd /var/www/paradisedollz

git status --short
git pull origin main
npm run build

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

You do not need `composer install` or `php artisan migrate` for those changes unless another pushed commit also changed dependencies or database migrations.
