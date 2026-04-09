<?php

namespace App\Providers;

use App\Repositories\Eloquent\PropertyRepository;
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PropertyRepositoryInterface::class, PropertyRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
