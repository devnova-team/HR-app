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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

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
    }
}
