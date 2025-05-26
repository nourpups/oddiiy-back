<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Observers\TransactionObserver;
use Goodoneuz\PayUz\Models\Transaction;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}
