<?php

namespace App\Observers;

use App\Action\SendOrderNotificationToTelegramAction;
use App\Enum\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements  ShouldHandleEventsAfterCommit
{
    public function __construct(public SendOrderNotificationToTelegramAction $sendOrderNotificationToTelegramAction)
    {
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    public function updated(Order $order): void
    {
        // Проверяем, изменилось ли поле status
        if ($order->isDirty('status') && $order->status !== OrderStatus::PENDING) {
            // Загружаем необходимые связи для формирования сообщения
            $order->load([
                'address',
                'coupon',
                'items' => [
                    'sku.product',
                    'skuVariant'
                ],
                'user'
            ]);

            ($this->sendOrderNotificationToTelegramAction)($order);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
