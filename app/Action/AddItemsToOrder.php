<?php

namespace App\Action;

use App\Models\Sku;
use App\Models\SkuVariant;
use Illuminate\Support\Facades\DB;

class AddItemsToOrder
{
      public function __invoke(array $items)
      {
          $unavailableItems = [];

          // Проверяем доступность каждого элемента
          foreach ($items as $item) {
              $skuId = $item['sku_id'];
              $skuVariantId = $item['sku_variant_id'] ?? null;
              $quantity = $item['quantity'];

              if ($skuVariantId) {
                  // Проверяем запас для SkuVariant
                  $skuVariant = SkuVariant::find($skuVariantId);
                  if (!$skuVariant || $quantity > $skuVariant->stock) {
                      $unavailableItems[] = $item;
                  }
              } else {
                  // Проверяем запас для Sku
                  $sku = Sku::find($skuId);
                  if (!$sku || $quantity > $sku->stock) {
                      $unavailableItems[] = $item;
                  }
              }
          }

          // Если есть недоступные элементы, возвращаем их
          if (!empty($unavailableItems)) {
              return [
                  'success' => false,
                  'unavailable_items' => $unavailableItems,
              ];
          }

          // Если все элементы доступны, декрементируем запасы в транзакции
          DB::beginTransaction();
          try {
              foreach ($items as $item) {
                  $skuId = $item['sku_id'];
                  $skuVariantId = $item['sku_variant_id'] ?? null;
                  $quantity = $item['quantity'];

                  if ($skuVariantId) {
                      $skuVariant = SkuVariant::find($skuVariantId);
                      $skuVariant->decrement('stock', $quantity);
                  } else {
                      $sku = Sku::find($skuId);
                      $sku->decrement('stock', $quantity);
                  }
              }
              DB::commit();
          } catch (\Exception $e) {
              DB::rollBack();
              return [
                  'success' => false,
                  'error' => 'Ошибка при обновлении запасов.',
              ];
          }

          return ['success' => true];
      }
}
