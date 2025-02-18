<?php

namespace Database\Seeders;

use App\Enum\SaleType;
use App\Models\Category;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fakers = [
            'ru' => Factory::create('ru_RU'),
            'uz' => fake(),
        ];

        Category::factory()
            ->count(5)
            ->create()
            ->map(function (Category $category) use ($fakers) {
                // создаём переводы
                /** @var \Faker\Generator $faker */
                foreach ($fakers as $locale => $faker) {
                    $name = $faker->firstName();
                    $category->translateOrNew($locale)->name = $name;
                    $category->translateOrNew($locale)->slug = str()->slug($name, $locale);
                }

                $category->save();
            });
    }
}
