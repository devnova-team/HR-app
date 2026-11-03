<?php

namespace App\Providers;

use App\Repositories\ConversationRepository;
use App\Repositories\ConversationRepositoryInterface;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\MessageRepository;
use App\Repositories\MessageRepositoryInterface;
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
