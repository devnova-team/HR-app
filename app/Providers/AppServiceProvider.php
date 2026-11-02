<?php

namespace App\Providers;

use App\Repositories\Interfaces\NotificationRepositoryInterface;
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
        $this->app->bind(NotificationRepositoryInterface::class,NotificationRepository::class
        );
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
