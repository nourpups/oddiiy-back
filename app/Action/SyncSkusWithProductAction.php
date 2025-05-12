<?php

namespace App\Action;

use App\Enum\RemoveKey;
use App\Models\Product;
use App\Models\Sku;
use App\Models\SkuVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SyncSkusWithProductAction
{
    public function __invoke(Product $product, array $skus): void
    {
        $incomingSkuIds = collect($skus)
            ->pluck('id')
            ->filter()
            ->toArray();

        // Удаление отсутствующих SKU
        $product->skus()
            ->whereNotIn('id', $incomingSkuIds)
            ->each(function (Sku $sku) {
                $sku->attributeOptions()->detach();
                $sku->variants()->delete(); // Удаляем варианты
                $sku->discount()?->delete();
                $sku->delete();
            });

        foreach ($skus as $skuData) {
            $sku = isset($skuData['id'])
                ? Sku::query()->findOrFail($skuData['id'])
                : $product->skus()->create([
                    'sku' => str()->random(11),
                    'price' => $skuData['price'],
                ]);

            if (isset($skuData['id'])) {
                $sku->update(['price' => $skuData['price']]);
            }

            if ($skuData['combinations'] === RemoveKey::REMOVE->value) {
                $skuVariantIds = $sku->variants->pluck('id');

                DB::table('attribute_option_sku_attribute')
                    ->whereIn('id', $skuVariantIds)
                    ->delete();
                $sku->variants()->delete();
            } else {
                $incomingCombinations = collect($skuData['combinations']);
                $existingSkuVariantIds = $incomingCombinations
                    ->where('id', "!=", 0)
                    ->pluck('id')
                    ->toArray();

                // синхронизировать варианты sku
                SkuVariant::query()
                    ->where('sku_id', $sku->id)
                    ->whereNotIn('id', $existingSkuVariantIds)
                    ->get()
                    ->each(static function (SkuVariant $skuVariant) {
                        $skuVariant->attributeOptions()->detach();
                        $skuVariant->delete();
                    });

                // создать новые варианты sku
                $incomingCombinations
                    ->where('id', "==", 0)
                    ->each(static function ($combination) use ($sku) {
                        /** @var SkuVariant $variant */
                        $variant = $sku->variants()->create([
                            'stock' => $combination['stock'],
                            'model_id' => null, // @todo: реализовать логику
                        ]);

                        $variant->attributeOptions()->attach($combination['options']);
                    });
            }

            // Обработка изображений
            $incoming = collect($skuData['images'] ?? []);
            $existingMediaIds = $incoming
                ->filter(fn($image) => is_string($image))
                ->values();

            $sku->media()
                ->whereNotIn('uuid', $existingMediaIds)
                ->each(fn($media) => $media->delete());

            $incoming
                ->filter(fn($image) => $image instanceof UploadedFile)
                ->each(function (UploadedFile $file) use ($sku) {
                    $file = UploadedFile::createFromBase($file);
                    $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

                    $sku
                        ->addMedia($file)
                        ->usingName($fileName)
                        ->usingFileName(str($fileName)->slug() . '.' . $ext)
                        ->toMediaCollection();
                });
        }
    }
}
