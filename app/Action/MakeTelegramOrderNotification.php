<?php

namespace App\Action;

use App\Enum\OrderStatus;
use App\Enum\SaleType;
use App\Helper\SaleHelper;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Number;

class MakeTelegramOrderNotification
{
    public function __invoke(Order $order): string
    {
        $createdAt = $order->created_at
            ->setTimezone('Asia/Tashkent')
            ->format('Y-m-d H:i');

        $commentRow = $order->comment ? "Izoh: $order->comment\n" : '';
        $orderCoupon = $order->coupon_id ? $order->coupon : null;
        $orderCashbackOption = $order->cashback_wallet_option_id ? $order->cashbackWalletOption : null;

        $subtotal = $order->items->reduce(
            static fn(int $carry, OrderItem $item) => $carry + ($item->price * $item->quantity),
            0
        );
        $couponRow = "";
        if ($orderCoupon) {
            $sale = SaleHelper::formatSale(
                $order->coupon,
                $subtotal
            );
            $couponSaleValue = $orderCoupon->type->value === SaleType::PERCENTAGE->value ? "({$orderCoupon->value}%)" : "";
            $couponRow = "Promo-kod: {$orderCoupon->code} -" . $this->formatPrice(
                    $sale['amount']
                ) . " " . $couponSaleValue;
        }
        $cashbackOptionRow = $orderCashbackOption ?
            "Keshbek hamyondan: -" . $this->formatPrice($orderCashbackOption->value)
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

        $status = $order->status->getLabel();

        return "Buyurtma raqami № {$order->id}\n"
            . "Buyurtma vaqti: {$createdAt}\n"
            . "Status: {$status}\n\n"
            . "Buyurtmachi ismi: {$order->user->name}\n"
            . "Buyurtmachi telefon raqami: {$order->user->phone}\n"
            . "Buyurtma rasmiylashtirilgan ism: {$order->recipient_name}\n\n"
            . "Buyurtma manzili: {$order->address->formatted}\n"
            . "{$flatInfoRow}\n"
            . "{$orderItemsText}\n\n"
            . "To'lov turi: {$order->payment->getLabel()}\n"
            . "Yetkazib berish turi: {$order->delivery->getLabel()}\n"
            . "Summa: {$this->formatPrice($subtotal)}\n"
            . "{$couponRow}\n"
            . "{$cashbackOptionRow}\n"
            . "Yakuniy summa: {$this->formatPrice($order->sum)}\n\n"
            . "{$commentRow}";
    }

    /**
     * @param Collection<OrderItem> $orderItems
     * @return string
     */
    private function getOrderItemsText(Collection $orderItems): string
    {
        return $orderItems->map(function (OrderItem $orderItem) {
            $skuVariant = $orderItem->sku_variant_id ? $orderItem->skuVariant : null;
            $sku = $orderItem->sku;

            $itemInfoRow = $skuVariant
                ? "{$skuVariant->sku->product->name} (" . implode(
                    " ",
                    $skuVariant->attributeOptions->pluck('value')->toArray()
                ) . ")\n"
                : "{$sku->product->name} ";

            $stock = $sku->stock ?: $skuVariant->stock;
            $itemInfoRow .= "Zaxira: $stock\n";

            // Check if the product has a discount to show the original price in <del>
            $originalPrice = $skuVariant ? $skuVariant->sku->price : $sku->price;
            $hasDiscount = $skuVariant ? !is_null($skuVariant->sku->product->discount) : !is_null(
                $sku->product->discount
            );
            $priceDisplay = $hasDiscount
                ? "{$this->formatPrice($orderItem->price)} <del>{$this->formatPrice($originalPrice)}</del>"
                : "{$this->formatPrice($orderItem->price)}";

            $itemInfoRow .= "Narxi: $priceDisplay";

            $itemPriceRow = "{$this->formatPrice($orderItem->price)} ✖️ {$orderItem->quantity} 🟰 " . $this->formatPrice(
                    $orderItem->price * $orderItem->quantity
                );

            return "{$itemInfoRow}\n{$itemPriceRow}";
        })->implode("\n");
    }

    private function formatPrice(int $price): string
    {
        return Number::format($price) . " so'm";
    }
}
