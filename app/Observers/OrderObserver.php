<?php

namespace App\Observers;

use App\Action\SendOrderNotificationToTelegramAction;
use App\Enum\OrderStatus;
use App\Models\CashbackWallet;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use App\Models\SkuVariant;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;

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
        if ($order->wasChanged('status') && $order->status !== OrderStatus::PENDING) {
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

        try {
            Log::info('es kontak', [
                't' => $order->wasChanged('status'),
                'x' =>  $order->status === OrderStatus::ACCEPTED,
                'd' => $order->status
            ]);
        // начисляем кэшбэк при оплате заказа
            if ($order->wasChanged('status') && $order->status === OrderStatus::ACCEPTED) {
                $userCashbackWallet = CashbackWallet::query()->where('user_id', $order->user_id)->first();
                Log::info("cashback wallet user_id = {$order->user_id}", ['w' => $userCashbackWallet]);
                $userCashbackWallet->update([
                    'balance' => $userCashbackWallet->balance + $order->sum / 100 * 2, // 2%
                    'total_earned' => $userCashbackWallet->total_earned + $order->sum / 100 * 2,
                ]);
                Log::info("cashback wallet user_id = {$order->user_id} updated", ['w' => $userCashbackWallet->fresh()]);
            }
        } catch (\Throwable $e) {
            Log::error("pizda {$e->getMessage()}", ['trace' => $e->getTrace()]);
        }

        // возвращаем зарезервированные товары обратно в запас
        if ($order->wasChanged('status') && $order->status === OrderStatus::CANCELLED) {
            $order->load('items.skuVariant');
            $order->items->each(static function (OrderItem $orderItem) {
                $skuId = $orderItem['sku_id'];
                $skuVariantId = $orderItem['sku_variant_id'];
                $quantity = $orderItem['quantity'];

                if ($skuVariantId) {
                    $skuVariant = SkuVariant::find($skuVariantId);
                    $skuVariant->increment('stock', $quantity);
                } else {
                    $sku = Sku::find($skuId);
                    $sku->increment('stock', $quantity);
                }
            });
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
