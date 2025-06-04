<?php

use App\Enum\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Order::query()
        ->where('status', OrderStatus::PENDING->value)
        ->where('created_at', '<=', now()->subHour())
        ->chunkById(100, function ($orders) {
            foreach ($orders as $order) {
                $order->update(['status' => OrderStatus::CANCELLED]);
                Log::info("Заказ с ID {$order->id} был отменен по причине таймаута");
            }
        });
})->everyMinute();
