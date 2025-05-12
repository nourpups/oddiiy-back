<?php

namespace App\Action;

use App\Enum\RemoveKey; // Предполагаем, что Enum определен
use App\Models\Sku;
use App\Models\SkuVariant;
use App\Models\AttributeOption;
use Illuminate\Support\Arr;

class UpdateSkuVariantsAction
{
    public function __invoke($combinations, Sku $sku): void
    {
        // combinations приходит с RemoveKey::REMOVE, если он пустой
        // Приходит он пустым в 2 случаях:
        // 1) если sku без атрибутов; 2) если у sku убрали все атрибуты
        if ($combinations === RemoveKey::REMOVE) {
            if ($sku->variants()->exists()) {
                $sku->variants()->delete(); // Удаляем все записи sku_variants для этого SKU
            }
            $sku->variants()->create([
                'stock' => $combinations['stock'],
                'photomodel_id' => null,
            ]);
            return;
        }
        $combinations = collect($combinations);
        // id = 3 [id: 3, options: [1,2], stock: 23], [id: 2, options: [1,3], stock: 34]
        // Загружаем существующие варианты для этого SKU
        $existingVariants = $sku->variants()
            ->with('attributeOptions')
            ->get()
            ->keyBy('id');

        // Загружаем все опции из combinations с их атрибутами
        $optionIds = $combinations->pluck('options')
            ->flatten()
            ->unique()
            ->toArray();
        $options = AttributeOption::query()->whereIn('id', $optionIds)
            ->with('attribute')
            ->get()
            ->keyBy('id');

        // Проверяем, есть ли атрибуты с is_options_multiselect = true
        $hasMultiselect = $options->some(fn($option) => $option->attribute->is_options_multiselect);

        if (!$hasMultiselect) {
            // Сценарий: Все атрибуты с is_options_multiselect = false
            $firstCombination = $combinations->first();
            $stock = $firstCombination->stock;

            // Удаляем старые варианты
            $sku->variants()->delete();

            // Создаем новую запись с общим запасом
            /** @var SkuVariant $skuVariant */
            $skuVariant = $sku->variants()->create([
                'stock' => $stock,
                'photomodel_id' => null,
            ]);

            // Привязываем все опции
            foreach ($optionIds as $optionId) {
                $skuVariant->attributeOptions()->syncWithoutDetaching([$optionId]);
            }
        } else {
            // Сценарии с is_options_multiselect = true (смешанные или только мультивыбор)
            $newVariants = [];
            foreach ($combinations as $combination) {
                $optionIds = $combination->options;
                $stock = $combination->stock;

                // Проверяем, существует ли уже вариант с такими опциями
                $existingVariant = $existingVariants->first(function ($variant) use ($optionIds) {
                    $variantOptionIds = $variant->attributeOptions
                        ->pluck('id')
                        ->sort()
                        ->values();
                    $newOptionIds = collect($optionIds)
                        ->sort()
                        ->values();
                    return $variantOptionIds->equals($newOptionIds);
                });

                if ($existingVariant) {
                    // Обновляем существующий вариант
                    $existingVariant->update(['stock' => $stock]);
                    $newVariants[$existingVariant->id] = $existingVariant;
                } else {
                    // Создаем новый вариант
                    $skuVariant = $sku->variants()->create([
                        'stock' => $stock,
                        'photomodel_id' => null,
                    ]);
                    foreach ($optionIds as $optionId) {
                        $skuVariant->attributeOptions()->attach($optionId);
                    }
                    $newVariants[$skuVariant->id] = $skuVariant;
                }
            }

            // Удаляем варианты, которые не вошли в новые комбинации
            $existingVariantIds = $existingVariants->keys();
            $newVariantIds = array_keys($newVariants);
            $variantsToDelete = array_diff($existingVariantIds->toArray(), $newVariantIds);
            SkuVariant::query()->whereIn('id', $variantsToDelete)->delete();
        }
    }
}
