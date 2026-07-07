# Hostinger VPS Deployment Guide

This project is deployed as a Laravel app on a Hostinger KVM VPS with Nginx,
MySQL, Redis, Supervisor, Laravel queues, and Laravel Reverb.

## 1. DNS

In Hostinger, point the domain to the VPS public IP:

- `A` record: `@` -> VPS IPv4
- `A` record: `www` -> VPS IPv4

Wait for DNS propagation before running Certbot.

## 2. First Server Setup

SSH into the VPS from your computer:

```bash
ssh root@YOUR_VPS_IP
```

Update the server and install the runtime packages:

```bash
apt update && apt upgrade -y
apt install -y nginx mysql-server redis-server supervisor git unzip curl rsync certbot python3-certbot-nginx
apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl
```

Install Composer:

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Create the app folder:

```bash
mkdir -p /var/www/paradisedollz
chown -R root:www-data /var/www/paradisedollz
chmod -R 775 /var/www/paradisedollz
```

Create the MySQL database and user:

```bash
mysql
```

```sql
CREATE DATABASE paradisedollz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'paradisedollz_user'@'localhost' IDENTIFIED BY 'REPLACE_WITH_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON paradisedollz.* TO 'paradisedollz_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 3. Production `.env`

Upload the repo once or create the `.env` manually:

```bash
cd /var/www/paradisedollz
nano .env
```

Use `deployment/.env.production` as the template, but replace every
`CHANGE_ME` value. Generate the app key with:

```bash
php artisan key:generate --show
```

Important values:

- `APP_URL=https://yourdomain.tld`
- `DB_PASSWORD=...`
- `SESSION_DOMAIN=yourdomain.tld`
- `REDIS_PASSWORD=...` or `null` if Redis has no password
- `RESEND_API_KEY=...`
- `REVERB_APP_SECRET=...`
- `REVERB_HOST=yourdomain.tld`
- `BUNNY_API_KEY=...`

## 4. Nginx

Copy the Nginx template:

```bash
cp /var/www/paradisedollz/deployment/nginx.conf /etc/nginx/sites-available/paradisedollz
nano /etc/nginx/sites-available/paradisedollz
```

Replace every `yourdomain.com` with the real domain.

For Ubuntu 24.04/PHP 8.3, make sure this line is:

```nginx
fastcgi_pass unix:/run/php/php8.3-fpm.sock;
```

Keep `client_max_body_size` at `64M` or higher so course access proof uploads can include multiple 10MB files.

Enable the site:

```bash
ln -s /etc/nginx/sites-available/paradisedollz /etc/nginx/sites-enabled/paradisedollz
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

Install SSL:

```bash
certbot --nginx -d yourdomain.tld -d www.yourdomain.tld
```

## 5. Supervisor

Copy the Supervisor configs:

```bash
cp /var/www/paradisedollz/deployment/supervisor/paradisedollz-queue.conf /etc/supervisor/conf.d/paradisedollz-queue.conf
cp /var/www/paradisedollz/deployment/supervisor/paradisedollz-scheduler.conf /etc/supervisor/conf.d/paradisedollz-scheduler.conf
cp /var/www/paradisedollz/deployment/supervisor/paradisedollz-reverb.conf /etc/supervisor/conf.d/paradisedollz-reverb.conf
supervisorctl reread
supervisorctl update
supervisorctl status
```

The queue worker sends queued emails. The scheduler checks once per minute for scheduled or recurring email campaigns. Both must show `RUNNING` for automated campaigns to work.

## 6. GitHub Secrets

In GitHub, open:

`Repo -> Settings -> Secrets and variables -> Actions -> New repository secret`

Add:

- `VPS_HOST`: VPS IP address
- `VPS_USER`: `root` for first setup, or your deploy user
- `VPS_PORT`: `22`
- `VPS_SSH_PRIVATE_KEY`: private key allowed to SSH into the VPS
- `DEPLOY_PATH`: `/var/www/paradisedollz`
- `VITE_REVERB_APP_KEY`: same as production `REVERB_APP_KEY`
- `VITE_REVERB_HOST`: your domain, without `https://`
- `VITE_REVERB_PORT`: `443`
- `VITE_REVERB_SCHEME`: `https`

The workflow deploys automatically after every push to `main`.

## 7. First Manual Deploy

After the first GitHub Actions deploy finishes, SSH into the server and check:

```bash
cd /var/www/paradisedollz
php artisan migrate:status
supervisorctl status
php artisan queue:failed
```

Visit:

```text
https://yourdomain.tld
```

## 8. Future Updates

Push to `main`:

```bash
git push origin main
```

GitHub Actions will build the app, upload it to Hostinger, run migrations,
refresh Laravel caches, restart queues, and reload services.
