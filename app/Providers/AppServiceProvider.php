<?php

namespace App\Providers;

use App\Repositories\ConversationRepository;
use App\Repositories\EmployeePushTokenRepository;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\EmployeePushTokenRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\MessageRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NotificationRepositoryInterface::class,NotificationRepository::class);
        $this->app->bind(ConversationRepositoryInterface::class,ConversationRepository::class);
        $this->app->bind(MessageRepositoryInterface::class,MessageRepository::class);
        $this->app->bind(EmployeePushTokenRepositoryInterface::class,EmployeePushTokenRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enable strict mode for Eloquent models in non-production environments to enforce stricter data handling and validation.
        Model::shouldBeStrict(! $this->app->environment('production'));

        //limiter
        RateLimiter::for('forgot-password', function(Request $request){
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(1)->by($email. '|' . $request->ip()), // Limit to 1 request per minute per email and IP address
                Limit::perHour(10)->by($request->ip()), // Limit to 10 requests per hour per IP address
            ];

        });

        RateLimiter::for('verify-otp', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
                Limit::perHour(30)->by($request->ip()),
            ];
        });

        RateLimiter::for('reset-password', function (Request $request) {

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(30)->by($request->ip()),
            ];
        });

    }
}
