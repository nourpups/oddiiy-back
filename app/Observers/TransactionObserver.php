<?php

namespace App\Observers;

use App\Enum\OrderStatus;
use App\Models\Order;
use Goodoneuz\PayUz\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    public function updated(Transaction $transaction): void
    {
        // Проверяем, изменился ли статус транзакции
        if ($transaction->wasChanged('state') &&
            $transaction->state !== Transaction::STATE_CREATED) {
            Log::info(sprintf(
                "Транзакция с ID %d (%s) переходит в статус %d",
                $transaction->id,
                $transaction->payment_system,
                $transaction->state,
            ));

            $order = Order::find($transaction->transactionable_id);
            if (empty($order)) {
                Log::error("Заказ с ID {$transaction->transactionable_id} не найден");
                return;
            }

            if ($transaction->state === Transaction::STATE_CANCELLED ||
                $transaction->state === Transaction::STATE_CANCELLED_AFTER_COMPLETE) {
                $order->update(['status' => OrderStatus::CANCELLED]);
                Log::info(sprintf(
                    "Заказ с ID %d отменен (status - %d)",
                    $order->id,
                    OrderStatus::CANCELLED->value,
                ));
            }

            if ($transaction->state === Transaction::STATE_COMPLETED) {
                $order->update(['status' => OrderStatus::ACCEPTED]);
                Log::info(sprintf(
                    "Заказ с ID %d оплачен (status - %d)",
                    $order->id,
                    OrderStatus::ACCEPTED->value,
                ));
            }
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
