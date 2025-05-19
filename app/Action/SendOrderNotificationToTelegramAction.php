<?php

namespace App\Action;

use App\Enum\SaleType;
use App\Helper\SaleHelper;
use App\Models\Order;
use App\Models\OrderItem;
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Database\Eloquent\Collection;

class SendOrderNotificationToTelegramAction
{
    public function __invoke(Order $order): void
    {
        $createdAt = $order->created_at
            ->setTimezone('Asia/Tashkent')
            ->format('Y-m-d H:i');

        $commentRow = $order->comment ? "Izoh: $order->comment" : '';
        $orderCoupon = $order->coupon_id ? $order->coupon : null;

        $subtotal = $order->items->reduce(
            static fn(int $carry, OrderItem $item) => $carry + ($item->price * $item->quantity),
            0
        );
        $couponRow = $orderCoupon ?
            "Promo-kod: {$orderCoupon->code} -" . SaleHelper::formatSale(
                $order->coupon,
                $subtotal
            )['amount'] . " so'm (" . $orderCoupon->value . ($orderCoupon->type->value === SaleType::PERCENTAGE->value ? "%" : "so'm") . ")"
            : "";

        $orderItemsText = $this->getOrderItemsText($order->items);
        $flatInfoRow = "";
        if ($order->address->entrance) {
            $flatInfoRow .= "Podyezd: {$order->address->entrance} ";
        }

        if ($order->address->floor) {
            $flatInfoRow .= "Qavat: {$order->address->floor} ";
        }

        if ($order->address->apartment) {
            $flatInfoRow .= "Xonadon: {$order->address->apartment} ";
        }

        if ($order->address->orientation) {
            $flatInfoRow .= "Mo'ljal: {$order->address->orientation} ";
        }

        $message = "Buyurtma raqami № {$order->id}\n"
            . "Buyurtma vaqti: {$createdAt}\n\n"
            . "Buyurtmachi ismi: {$order->user->name}\n"
            . "Buyurtmachi telefon raqami: {$order->user->phone}\n"
            . "Buyurtma rasmiylashtirilgan ism: {$order->recipient_name}\n\n"
            . "Buyurtma manzili: {$order->address->formatted}\n"
            . "{$flatInfoRow}\n\n"
            . "{$orderItemsText}\n\n"
            . "To'lov turi: {$order->payment->getLabel()}\n"
            . "Yetkazib berish turi: {$order->delivery->getLabel()}\n"
            . "Summa: {$subtotal}\n"
            . "{$commentRow}\n"
            . "{$couponRow}\n"
            . "Yakuniy summa: {$order->sum}";

        Telegraph::chat(config('telegram_bot.send_order.group_id'))
            ->message($message)
            ->send();
    }

    /**
     * @param Collection<OrderItem> $orderItems
     * @return string
     */
    private function getOrderItemsText(Collection $orderItems): string
    {
        return $orderItems->map(static function (OrderItem $orderItem) {
            $skuVariant = $orderItem->sku_variant_id ? $orderItem->skuVariant : null;
            $sku = $orderItem->sku;

            $itemInfoRow = $skuVariant
                ? "{$skuVariant->sku->product->name} (" . implode(
                    " ",
                    $skuVariant->attributeOptions->pluck('value')->toArray()
                ) . ")\n"
                : "{$sku->product->name} ";

            // Check if the product has a discount to show the original price in <del>
            $originalPrice = $skuVariant ? $skuVariant->sku->price : $sku->price;
            $hasDiscount = $skuVariant ? !is_null($skuVariant->sku->product->discount) : !is_null(
                $sku->product->discount
            );
            $priceDisplay = $hasDiscount
                ? "{$orderItem->price} <del>{$originalPrice}</del>"
                : "{$orderItem->price}";

            $itemInfoRow .= "Narxi: $priceDisplay";

            $itemPriceRow = "{$orderItem->price} ✖️ {$orderItem->quantity} 🟰 " . ($orderItem->price * $orderItem->quantity);

            return "{$itemInfoRow}\n{$itemPriceRow}";
        })->implode("\n");
    }
}
