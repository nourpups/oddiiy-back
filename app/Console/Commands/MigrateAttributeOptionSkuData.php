<?php

namespace App\Console\Commands;

use App\Models\SkuVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateAttributeOptionSkuData extends Command
{
    protected $signature = 'app:migrate-attribute-option-sku-data';

    protected $description = 'Трансформировать данные из attribute_option_sku в sku_variants и attribute_option_sku_variant';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            // Получаем все уникальные product_id из skus, у которых есть записи в attribute_option_sku
            $productIds = DB::table('skus')
                ->join('attribute_option_sku', 'skus.id', '=', 'attribute_option_sku.sku_id')
                ->distinct()
                ->pluck('skus.product_id');

            foreach ($productIds as $productId) {
                // Получаем все sku_id для текущего product_id
                $skuIds = DB::table('skus')
                    ->where('product_id', $productId)
                    ->pluck('id');

                // Получаем все записи для этих sku_id
                $options = DB::table('attribute_option_sku')
                    ->join('attribute_options', 'attribute_option_sku.attribute_option_id', '=', 'attribute_options.id')
                    ->join('attributes', 'attribute_options.attribute_id', '=', 'attributes.id')
                    ->whereIn('attribute_option_sku.sku_id', $skuIds)
                    ->select(
                        'attribute_option_sku.id',
                        'attribute_option_sku.sku_id',
                        'attribute_option_sku.attribute_option_id',
                        'attributes.is_options_multiselect'
                    )
                    ->get();

                // Группируем опции по sku_id
                $optionsBySku = $options->groupBy('sku_id');

                // Разделяем опции по типу multiselect для каждого sku_id
                $combinations = [];
                foreach ($optionsBySku as $skuId => $skuOptions) {
                    $singleSelectOptions = $skuOptions->where('is_options_multiselect', false);
                    $multiSelectOptions = $skuOptions->where('is_options_multiselect', true);

                    // Формируем комбинацию атрибутов для текущего sku_id
                    $combination = [
                        'sku_id' => $skuId,
                        'single_select' => $singleSelectOptions->pluck('attribute_option_id')->sort()->values()->toArray(),
                        'multi_select' => $multiSelectOptions->pluck('attribute_option_id')->sort()->values()->toArray(),
                    ];
                    $combinations[] = $combination;
                }

                // Группируем комбинации по уникальным наборам атрибутов
                $uniqueCombinations = $this->groupCombinations($combinations);

                // Обрабатываем уникальные комбинации
                foreach ($uniqueCombinations as $uniqueCombination) {
                    $this->processCombination($productId, $uniqueCombination);
                }
            }

            $this->info('Миграция прошла успешно');
            DB::commit();
        } catch (\Throwable $exception) {
            Log::error("Ошибка при выполнении скрипта: {$exception->getMessage()}", [
                'trace' => $exception->getTrace(),
            ]);
            DB::rollBack();
        }
    }

    /**
     * Группируем комбинации по уникальным наборам атрибутов.
     */
    private function groupCombinations(array $combinations): array
    {
        $grouped = [];

        foreach ($combinations as $combination) {
            // Создаем ключ для группировки на основе отсортированных атрибутов
            $key = json_encode([
                'single_select' => $combination['single_select'],
                'multi_select' => $combination['multi_select'],
            ]);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'sku_ids' => [],
                    'single_select' => $combination['single_select'],
                    'multi_select' => $combination['multi_select'],
                ];
            }

            $grouped[$key]['sku_ids'][] = $combination['sku_id'];
        }

        return array_values($grouped);
    }

    /**
     * Обрабатываем уникальную комбинацию атрибутов.
     */
    private function processCombination(int $productId, array $combination): void
    {
        // Берем первый sku_id из группы для создания записи в sku_variants
        $skuId = $combination['sku_ids'][0];

        // Создаем запись в sku_variants
        $skuVariant = SkuVariant::query()->create([
            'sku_id' => $skuId,
            'photomodel_id' => null,
            'stock' => 0,
        ]);

        // Создаем записи в attribute_option_sku_variant для single select опций
        foreach ($combination['single_select'] as $attributeOptionId) {
            DB::table('attribute_option_sku_variant')->insert([
                'sku_variant_id' => $skuVariant->id,
                'attribute_option_id' => $attributeOptionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Создаем записи в attribute_option_sku_variant для multi select опций
        foreach ($combination['multi_select'] as $attributeOptionId) {
            DB::table('attribute_option_sku_variant')->insert([
                'sku_variant_id' => $skuVariant->id,
                'attribute_option_id' => $attributeOptionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
