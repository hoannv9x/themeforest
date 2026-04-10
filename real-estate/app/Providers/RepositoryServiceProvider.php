<?php

namespace App\Providers;

use App\Repositories\Eloquent\AgentRepository;
use App\Repositories\Eloquent\PropertyRepository;
use App\Repositories\Interfaces\AgentRepositoryInterface;
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
        $this->app->bind(AgentRepositoryInterface::class, AgentRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
