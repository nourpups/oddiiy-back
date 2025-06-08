<?php

namespace App\Action;

use App\Enum\OrderStatus;
use App\Models\Order;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class SendOrderNotificationToTelegramAction
{
    public function __construct(public MakeTelegramOrderNotification $makeTelegramOrderNotification)
    {
    }

    public function __invoke(Order $order): void
    {
        $message = ($this->makeTelegramOrderNotification)($order);

        if (is_null($order->telegram_message_id)) {
            $response = Telegraph::chat(config('telegram_bot.send_order.group_id'))
                ->message($message)
                ->keyboard($this->buildKeyboard($order->status, $order->id))
                ->send();
        } else {
            $response = Telegraph::chat(config('telegram_bot.send_order.group_id'))
                ->edit($order->telegram_message_id)
                ->message($message)
                ->keyboard($this->buildKeyboard($order->status, $order->id))
                ->send();
        }

        Order::withoutEvents(function () use ($order, $response) {
            $order->update(['telegram_message_id' => $response->telegraphMessageId()]);
        });
    }

    private function buildKeyboard(OrderStatus $status, int $orderId): Keyboard
    {
        $keyboard = Keyboard::make();

        if ($status === OrderStatus::PENDING || $status === OrderStatus::ACCEPTED) {
            $keyboard->buttons([
                Button::make('Bekor qilish ❌')
                    ->width(.5)
                    ->action('changeStatus')
                    ->param('order_id', $orderId)
                    ->param('status', OrderStatus::CANCELLED->value),
            ]);
        }

        if ($status === OrderStatus::PENDING) {
            $keyboard->buttons([
                Button::make('Rasmiylashtirish ✅')
                    ->width(.5)
                    ->action('changeStatus')
                    ->param('order_id', $orderId)
                    ->param('status', OrderStatus::ACCEPTED->value),
            ]);
        }

        if ($status === OrderStatus::ACCEPTED) {
            $keyboard->buttons([
                Button::make('Yakunlash 🎉')
                    ->width(.5)
                    ->action('changeStatus')
                    ->param('order_id', $orderId)
                    ->param('status', OrderStatus::COMPLETED->value),
            ]);
        }

        return $keyboard;
    }
}
