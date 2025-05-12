<?php

namespace App\Action;

use App\Models\Sku;
use App\Models\SkuVariant;

class CreateSkuVariantsAction
{
    public function __invoke(array $combinations, Sku $sku): void
    {
        $combinations = collect($combinations);

        $combinations->each(static function ($combination) use ($sku) {
            /** @var SkuVariant $variant */
            $variant = $sku->variants()->create([
                'stock' => $combination['stock'],
                'photomodel_id' => null, // @todo: добавить реализацию
            ]);

            $variant->attributeOptions()->attach($combination['options']);
        });
    }
}
