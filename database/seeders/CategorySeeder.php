<?php

namespace Database\Seeders;

use App\Enum\DiscordEmotes;
use App\Enum\SaleType;
use App\Models\Category;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Mmo\Faker\FakeimgProvider;

class CategorySeeder extends Seeder
{
    public Generator|FakeimgProvider $faker;

    public function __construct()
    {
        $this->faker = app(Generator::class);
    }

    public function run(): void
    {
        $fakers = [
            'ru' => Factory::create('ru_RU'),
            'uz' => fake(),
        ];

        Category::factory()
            ->count(5)
            ->create()
            ->each(function (Category $category) use ($fakers) {
                // создаём переводы
                /** @var \Faker\Generator $faker */
                foreach ($fakers as $locale => $faker) {
                    $name = $faker->firstName();
                    $category->translateOrNew($locale)->name = $name;
                    $category->translateOrNew($locale)->slug = str()->slug($name, $locale);
                }

                $category->save();

                $discordEmotes = collect(
                    array_map(
                        static fn($emote) => $emote->value,
                        DiscordEmotes::cases()
                    )
                );

                $fakeImageUrl = $this->prepareFakeImgUrl($category->name);
                $imageName = sprintf(
                    '%s %s',
                    $category->name,
                    $discordEmotes->random()
                );

                $category
                    ->addMediaFromUrl($fakeImageUrl)
                    ->usingName($imageName)
                    ->usingFileName(str($name)->slug() . '.png')
                    ->toMediaCollection('mainImage');
            });
    }

    /**
     * Пакет предоставлящий провайдер для fakeimg
     * не имеет в себе функционал изменения font-size
     * хотя API fakeimg это поддерживает
     *
     * @param $text
     * @return string
     */
    private function prepareFakeImgUrl($text): string
    {
        $customFontSize = '&font_size=40';
        $imageUrlWithoutFontSize = $this->faker->fakeImgUrl(
            width: 500,
            height: 500,
            text: $text
        );
        return sprintf(
            '%s%s',
            $imageUrlWithoutFontSize,
            $customFontSize,
        );
    }
}
