<?php

namespace App\Http\Controllers;

use App\Action\AddItemsToOrder;
use App\Action\SendOrderNotificationToTelegramAction;
use App\Enum\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\CashbackWalletOption;
use App\Http\Resources\OrderResource;
use App\Models\CashbackWallet;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $orders = auth()->user()
            ->orders()
            ->with([
                'address',
                'coupon',
                'items' => [
                    'sku.product',
                    'skuVariant'
                ],
                'user',
                'cashbackWalletOption'
            ])
            ->where('status', '!=', OrderStatus::CANCELLED->value)
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function store(
        StoreOrderRequest $request,
        SendOrderNotificationToTelegramAction $sendOrderNotificationToTelegramAction,
        AddItemsToOrder $addItemsToOrder
    ): OrderResource|JsonResponse {
        $validated = $request->validated();
        Log::info('creating order', $validated);

        DB::beginTransaction();

        try {
            $order = Order::query()->create([
                'user_id' => $validated['user_id'],
                'coupon_id' => $validated['coupon_id'] ?? null,
                'cashback_wallet_option_id' => $validated['cashback_wallet_option_id'] ?? null,
                'recipient_name' => $validated['recipient_name'],
                'delivery' => $validated['delivery'],
                'payment' => $validated['payment'],
                'sum' => $validated['sum'],
                'comment' => $validated['comment'] ?? null,
                'status' => OrderStatus::PENDING,
            ]);

            if (!is_null($validated['cashback_wallet_option_id'])) {
                $userCashbackWallet = CashbackWallet::query()->where('user_id', $order->user_id)->first();
                $option = CashbackWalletOption::query()->find($validated['cashback_wallet_option_id']);

                if ($userCashbackWallet->balance < $option->value) {
                    return response()->json([
                        'message' => __('messages.invalidCashbackApplyAmount')
                    ], 400);
                }

                $userCashbackWallet->update([
                    'balance' => $userCashbackWallet->balance - $option->value,
                    'total_used' => $userCashbackWallet->total_earned + $option->value,
                ]);
            }

            $order->address()->create($validated['address']);

            // добавление элементов заказа в сам заказ
            $data = $addItemsToOrder($validated['items']);
            if (!$data['success']) {
                $unavailableSkuIds = collect($data['unavailable_items'])
                    ->pluck('sku_id')
                    ->toArray();
                $products = Product::query()->whereHas('skus', static function (Builder $q) use ($unavailableSkuIds) {
                    $q->whereIn('id', $unavailableSkuIds);
                })->get();
                $productNames = $products->implode('name', ',');
                return response()->json([
                    'message' => __("messages.order.unavailableItems", ['products' => $productNames])
                ], 400);
            }

            $order->items()->createMany($validated['items']);

            DB::commit();

            $order->load([
                'address',
                'cashbackWalletOption',
                'items' => [
                    'sku.product',
                    'skuVariant'
                ],
                'user',
                'coupon',
            ]);

            defer(static function () use ($order, $sendOrderNotificationToTelegramAction) {
                $sendOrderNotificationToTelegramAction($order);
            });

            Log::info('order created', $order);
            return new OrderResource($order);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error("Ошибка при создании заказа: {$e->getMessage()}", [
                'trace' => $e->getTrace(),
            ]);

            return response()->json([
                'message' => __('messages.unknown')
            ], 500);
        }
    }

    public function show(string $locale, Order $order): OrderResource
    {
        $order->load([
            'address',
            'cashbackWalletOption',
            'items' => [
                'sku.product',
                'skuVariant'
            ],
            'user',
            'coupon',
        ]);

        return new OrderResource($order);
    }
}
