<?php

namespace App\Providers;

use App\Models\ModelApplication;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/marketing_helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::model('application', ModelApplication::class);

        $this->configureRateLimiting();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('apply-submissions', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('chatter-applications', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email')));

            return [
                Limit::perHour(5)->by('chatter-ip|'.$request->ip()),
                Limit::perDay(3)->by('chatter-email|'.hash('sha256', $email)),
            ];
        });

        RateLimiter::for('chatter-clock', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password-reset-update', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('admin-actions', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('member-progress', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('course-access-requests', function (Request $request) {
            return Limit::perHour(3)->by(($request->user()?->id ?: $request->ip()).'|'.(string) $request->route('slug'));
        });

        RateLimiter::for('profile-updates', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('confirm-password', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('translation', function (Request $request) {
            return Limit::perMinute(60)->by('translation|'.$request->ip());
        });
    }
}
