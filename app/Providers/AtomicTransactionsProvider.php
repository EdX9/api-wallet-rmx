<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Database\TransactionService;
use App\Services\AtomicTransaction\AtomicTransactionDatabaseService;

class AtomicTransactionsProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AtomicTransactionDatabaseService::class, function ($app) {
            return new AtomicTransactionDatabaseService(
                app(lockService::class),
                new TransactionService()
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
