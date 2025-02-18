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
                'name' => [
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

            // ... ещё атрибуты
        ];

        foreach ($attributes as $attribute) {
            DB::transaction(static function () use ($attribute) {
                $createdAttribute = Attribute::factory()->create($attribute['name']);

                foreach ($attribute['options'] as $option) {
                    $ao = $createdAttribute->attributeOptions()->create($option);
                    $ao->load('translations');
                }
            });
        }
    }
}
