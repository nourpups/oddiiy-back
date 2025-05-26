<?php

namespace App\Observers;

use App\Enum\OrderStatus;
use App\Models\Order;
use Goodoneuz\PayUz\Models\Transaction;

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
        if ($transaction->isDirty('status') &&
            $transaction->status !== Transaction::STATE_CREATED) {
            $order = Order::find($transaction->transactionable_id);

            if ($order) {
                if ($transaction->status === -1 || $transaction->status === -2) {
                    $order->update(['status' => OrderStatus::CANCELLED]);
                } elseif ($transaction->status === 2) {
                    $order->update(['status' => OrderStatus::ACCEPTED]);
                }
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
