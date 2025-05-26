<?php

namespace App\Telegram;

use App\Action\MakeTelegramOrderNotification;
use App\Enum\OrderStatus;
use App\Models\Order;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\Log;

class Handler extends WebhookHandler
{
    public function __construct(public MakeTelegramOrderNotification $makeTelegramOrderNotification)
    {
        parent::__construct();
    }

    public function changeStatus(): void
    {
        $orderId = $this->data->get('order_id');
        $status = $this->data->get('status');

        $order = Order::with([
            'address',
            'coupon',
            'items' => [
                'sku.product',
                'skuVariant'
            ],
            'user'
        ])->find($orderId);

        $statusLabel = OrderStatus::from($status)->getLabel();
        $this->reply("Buyurtma statusi «{$statusLabel}»ga o'gartirildi ✅");

        $order->update(['status' => $status]);
    }
}
