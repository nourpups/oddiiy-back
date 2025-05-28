<?php

namespace App\Observers;

use App\Action\SendOrderNotificationToTelegramAction;
use App\Enum\OrderStatus;
use App\Models\CashbackWallet;
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
                'cashbackWalletOption',
                'coupon',
                'items' => [
                    'sku.product',
                    'skuVariant'
                ],
                'user'
            ]);

            ($this->sendOrderNotificationToTelegramAction)($order);
        }

        // начисляем кэшбэк при удовлетворитльной сумме заказа
        if ($order->isDirty('status') && $order->status === OrderStatus::ACCEPTED) {
            if ($order->sum >= 500_000) {
                $userCashbackWallet = CashbackWallet::query()->where('user_id', $order->user_id)->first();
                $userCashbackWallet->update([
                    'balance' => $userCashbackWallet->balance + $order->sum / 100 * 2, // 2%
                    'total_earned' => $userCashbackWallet->total_earned + $order->sum / 100 * 2,
                ]);
            }
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
