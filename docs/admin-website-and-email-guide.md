# Paradise Dolls Website Administration Guide

This guide explains what an administrator can manage in the website dashboard, how to send marketing email campaigns, and which changes still require a developer.

## 1. Accessing The Admin Panel

1. Open the Paradise Dolls website in a browser.
2. Select **Log In**.
3. Enter the administrator email address and password.
4. After login, the website opens the **Admin Panel**.

Only accounts with the `admin` role can access these controls. Do not share an administrator password. Each administrator should have their own account where possible.

## 2. Admin Panel Areas

### Overview

Shows the main activity and status summary for the website.

### Applications

- Review new applications.
- View submitted application photos.
- Approve or reject an application.
- Resend an approval email.
- Delete an application when appropriate.

Approving an application creates the model account and starts the onboarding process.

### Referrals

- Review referral activity.
- Search models and referral codes.
- View pending, joined, eligible, and paid referral states.

### Onboarding

- Review submitted model information and photos.
- Review identity verification documents.
- Request verification or resubmission.
- Approve verification.
- Send community invitations and record role assignment.
- Unlock or lock course access.
- Select **Edit Onboarding Form** to change editable onboarding questions and checkbox options.

Core legal, identity, and account fields remain fixed to protect existing records.

### Members

- Review model account progress.
- Update login details where allowed.
- Generate a new temporary password.
- Remove an account when necessary.

### Courses

- Create and edit courses.
- Add, edit, reorder, or remove modules and lessons.
- Upload lesson files and media.
- Publish or hide course content.
- Preview the member experience.

### Testimonials

- Review testimonials submitted by members.
- Approve and publish genuine reviews.
- Unpublish or delete a review.
- Create an administrator-managed success story when required.

Member reviews use the submitting model's saved profile photo.

### Site Editor

Use **Site Editor** to update public website content without editing code.

- Select the page to edit.
- Change text, headings, buttons, links, and supported images.
- Save the page.
- Open the public page in a separate browser tab and verify desktop and mobile layouts.

The Site Editor covers the configured public content fields. New page layouts, new form behavior, database changes, and new features still require a developer.

### Community Chat

Opens the private community area and moderation controls available to the administrator.

## 3. Email Campaigns

Open **Admin Panel > Email Campaigns**.

### Create A Campaign

1. Select **New Campaign**.
2. Enter an internal campaign name.
3. Choose the recipients:
   - **All models** sends to every model who has not unsubscribed.
   - **Fully onboarded models** sends only to models whose onboarding and community role assignment are complete.
4. Enter the email subject and message.
5. Optionally add a button label and an `https://` button link.
6. Optionally enter a repeat interval in days.
7. Choose **Save as draft**, **Send now**, or **Schedule**.
8. Select **Create Campaign**.

The text `{name}` can be placed in the subject or message. It is replaced with each recipient's name when the email is sent.

### One-Time Campaigns

Leave **Repeat every (days)** empty. The campaign completes after its first delivery run.

### Recurring Campaigns

Enter a number such as `3`, `7`, or `30` in **Repeat every (days)**. The same approved email content is sent again after that interval until the campaign is paused.

Use separate campaigns when the marketing message changes. Do not leave an outdated recurring campaign active.

### Send, Schedule, Pause, And Resume

- **Send Now** immediately creates a delivery run and places each recipient email on the queue.
- **Set Date** schedules the next run for the selected date and time.
- **Pause** stops future recurring or scheduled runs without deleting history.
- **Resume** continues a paused campaign. If its saved date has passed, it becomes due immediately.

### Delivery History

Each run records:

- Total recipients.
- Successfully sent emails.
- Failed emails.
- Skipped recipients, such as someone who unsubscribed after the run was queued.

The email content is copied into each run before queueing. Editing the campaign later does not change emails already queued.

### Unsubscribing

Every marketing email includes a signed **Manage email preferences** link. When a model unsubscribes:

- Future marketing campaigns exclude her automatically.
- Account, security, application, onboarding, verification, and course emails can still be sent when required.

Do not manually re-add a person to marketing emails without her permission.

## 4. Content Review Checklist

Before publishing website content or sending a campaign:

1. Check spelling, names, dates, and links.
2. Confirm the correct audience.
3. Open every button link.
4. Check that the message is still current.
5. Send a small internal test when the content is sensitive or time-specific.
6. Confirm that contact details are correct.

## 5. Changes That Require A Developer

Contact the developer for:

- New page designs or major layout changes.
- New application or onboarding workflows.
- New database fields outside the onboarding form editor.
- Payment, domain, DNS, SSL, mail-server, or VPS changes.
- Errors, failed migrations, inaccessible admin pages, or repeated email failures.
- Restoring backups or recovering deleted data.

Do not edit PHP, Blade, JavaScript, CSS, `.env`, Nginx, MySQL, or Supervisor files unless you are responsible for the technical deployment.

## 6. VPS Requirements For Automated Emails

Automated campaigns require all three services:

- Laravel web application.
- Queue worker: sends the individual emails.
- Laravel scheduler: detects campaigns that are due.

Check them on the VPS:

```bash
cd /var/www/paradisedollz
supervisorctl status
php artisan schedule:list
php artisan queue:failed
```

Expected Supervisor processes include:

```text
paradisedollz-queue
paradisedollz-scheduler
paradisedollz-reverb
```

All should show `RUNNING`.

## 7. Deploying Website Updates

After the latest code has been pushed to the `main` branch:

```bash
cd /var/www/paradisedollz
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
supervisorctl reread
supervisorctl update
supervisorctl restart paradisedollz-queue:*
supervisorctl restart paradisedollz-scheduler:*
```

For the first deployment of email campaigns, install the scheduler configuration once:

```bash
cp /var/www/paradisedollz/deployment/supervisor/paradisedollz-scheduler.conf /etc/supervisor/conf.d/paradisedollz-scheduler.conf
supervisorctl reread
supervisorctl update
supervisorctl status
```

## 8. Troubleshooting Email Campaigns

### A Scheduled Campaign Did Not Start

```bash
supervisorctl status
php artisan schedule:list
php artisan email-campaigns:dispatch
```

If the manual dispatch works, check the `paradisedollz-scheduler` Supervisor process and log.

### Emails Are Queued But Not Sent

```bash
supervisorctl status
php artisan queue:failed
tail -n 100 /var/log/supervisor/paradisedollz-queue.log
```

Confirm the production mail settings in `.env`, then clear cached configuration after any approved change:

```bash
php artisan config:clear
php artisan config:cache
php artisan queue:restart
```

### Some Emails Failed

Check the campaign's delivery history and the failed queue list. A failure may be caused by an invalid address, mail-provider rejection, rate limit, or incorrect mail credentials.

Never place passwords, API keys, SMTP credentials, or private model information in this guide or in Git.
