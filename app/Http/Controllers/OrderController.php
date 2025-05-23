<?php

namespace App\Http\Controllers;

use App\Action\SendOrderNotificationToTelegramAction;
use App\Enum\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $orders = auth()->user()->orders()->with([
            'address',
            'items' => [
                'sku.product',
                'skuVariant'
            ],
            'user'
        ])->get();

        return OrderResource::collection($orders);
    }

    public function store(
        StoreOrderRequest $request,
        SendOrderNotificationToTelegramAction $sendOrderNotificationToTelegramAction
    ): OrderResource|JsonResponse {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $order = Order::query()->create([
                'user_id' => $validated['user_id'],
                'coupon_id' => $validated['coupon_id'] ?? null,
                'recipient_name' => $validated['recipient_name'],
                'delivery' => $validated['delivery'],
                'payment' => $validated['payment'],
                'sum' => $validated['sum'],
                'comment' => $validated['comment'] ?? null,
                'status' => OrderStatus::PENDING,
            ]);

            $order->address()->create($validated['address']);
            $order->items()->createMany($validated['items']);

            DB::commit();

            $order->load([
                'address',
                'items' => [
                    'sku.product',
                    'skuVariant'
                ],
                'user',
                'coupon',
            ]);

//            defer(static function () use ($order, $sendOrderNotificationToTelegramAction) {
//                $sendOrderNotificationToTelegramAction($order);
//            });

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
            'items' => [
                'sku.product',
                'skuVariant'
            ],
            'user',
            'coupon',
        ]);

        return new OrderResource($order);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    public function destroy(Order $order)
    {
        //
    }
}
