<?php

namespace App\Action;

use App\Enum\RemoveKey;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\UploadedFile;

class SyncSkusWithProductAction
{
    public function __invoke(Product $product, array $skus): void
    {
        $incomingSkuIds = collect($skus)
            ->pluck('id')
            ->filter()
            ->toArray();

        // Удаляем все старые SKU, которых нет в новых данных
        $product->skus()
            ->whereNotIn('id', $incomingSkuIds)
            ->each(function (Sku $sku) {
                $sku->attributeOptions()->detach();
                $sku->discount()->delete();
                $sku->delete();
            });

        // Обрабатываем каждый sku
        foreach ($skus as $skuData) {
            if (isset($skuData['id'])) {
                $sku = Sku::query()->findOrFail($skuData['id']);
                $sku->update([
                    'price' => $skuData['price'],
                ]);
            } else {
                $sku = $product->skus()->create([
                    'sku' => str()->random(11),
                    'price' => $skuData['price'],
                ]);
            }

            // Обновляем атрибуты
            if (array_key_exists('attributes', $skuData)) {
                // очистить если возвращается специальный ключ
                if ($skuData['attributes'] === RemoveKey::REMOVE->value) {
                    $sku->attributeOptions()?->detach();
                } else {
                    $sku->attributeOptions()->sync($skuData['attributes']);
                }
            }

            // Обновляем изображения
            $incoming = collect($skuData['images'] ?? []);
            $existingMediaIds = $incoming
                ->filter(fn($image) => is_string($image))
                ->map(fn($uuid) => $uuid)
                ->values();

            $sku->media()
                ->whereNotIn('uuid', $existingMediaIds)
                ->each(fn($media) => $media->delete());

            $incoming
                ->filter(fn($image) => $image instanceof UploadedFile)
                ->each(function (UploadedFile $file) use ($sku) {
                    $fileName = $file->getClientOriginalName();
                    $sku
                        ->addMedia($file)
                        ->usingName($fileName)
                        ->usingFileName(str($fileName)->slug() . '.' . $file->getClientOriginalExtension())
                        ->toMediaCollection();
                });
        }
    }
}
