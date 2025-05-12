<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Атрибут пока только один - Цвет
        $attributes = [
            [
                'is_options_multiselect' => false,
                'translations' => [
                    'ru' => ['name' => 'Цвет'],
                    'uz' => ['name' => 'Rang'],
                ],
                'options' => [
                    [
                        'ru' => ['value' => 'Белый'],
                        'uz' => ['value' => 'Oq'],
                    ],
                    [
                        'ru' => ['value' => 'Черный'],
                        'uz' => ['value' => 'Qora'],
                    ],
                    [
                        'ru' => ['value' => 'Голубой'],
                        'uz' => ['value' => 'Havorang'],
                    ]
                ]
            ],
            [
                'is_options_multiselect' => true,
                'translations' => [
                    'ru' => ['name' => 'Размер'],
                    'uz' => ['name' => "O'lcham"],
                ],
                'options' => [
                    [
                        'ru' => ['value' => 'XL (160/175)'],
                        'uz' => ['value' => 'XL (160/175)'],
                    ],
                    [
                        'ru' => ['value' => 'XXL (176/186)'],
                        'uz' => ['value' => 'XXL (176/186)'],
                    ],
                ]
            ],
        ];

        foreach ($attributes as $attribute) {
            DB::transaction(static function () use ($attribute) {
                $createdAttribute = Attribute::factory()->create([
                    'is_options_multiselect' => $attribute['is_options_multiselect'],
                    ...$attribute['translations'],
                ]);

                foreach ($attribute['options'] as $option) {
                    $createdAttribute->options()->create($option);
                }
            });
        }
    }
}
