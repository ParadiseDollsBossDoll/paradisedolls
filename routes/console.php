<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email? : Recipient address (defaults to MAIL_FROM_ADDRESS)}', function () {
    $to = $this->argument('email') ?: config('mail.from.address');

    Mail::raw('Paradise Dolls mail test - if you received this, outbound mail is configured.', function ($message) use ($to) {
        $message->to($to)->subject(config('app.name').' mail test');
    });

    $this->components->info('Sent via '.config('mail.default').' to '.$to);
})->purpose('Send one test email using the configured mail driver');
