<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AtomicTransaction\AtomicBalanceUpdate;

class AtomicBalanceUpdateProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AtomicBalanceUpdate::class, function ($app) {
            return new AtomicBalanceUpdate(
                app(AtomicTransactionDatabaseService::class),
                app(MathService::class),
                config()
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
