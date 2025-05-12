<?php

namespace Database\Seeders;

use App\Enum\DiscordEmotes;
use App\Enum\Locale;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sku;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Mmo\Faker\FakeimgProvider;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ProductSeeder extends Seeder
{
    public Generator|FakeimgProvider $faker;

    public function __construct()
    {
        $this->faker = app(Generator::class);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Category::all()->each(function (Category $category) {
                Product::factory()
                    ->count(mt_rand(1, 3))
                    ->for($category)
                    ->create()
                    ->each(function (Product $product) {
                        // создаём переводы
                        $this->createProductTranslations($product);
                        // создаём sku
                        $this->createProductVariants($product);

                        dump('Usage: ' . memory_get_usage() / 1024 / 1024 . ' MBs');
                        gc_collect_cycles(); // убирает проблему с memory limit exceeded
                    });
            });
            dump('Peak usage: ' . memory_get_peak_usage() / 1024 / 1024 . ' MBs');
        });
    }

    private function createProductTranslations($product): void
    {
        $fakers = [
            Locale::RU->value => Factory::create('ru_RU'),
            Locale::UZ->value => fake(),
        ];

        /** @var \Faker\Generator $faker */
        foreach ($fakers as $locale => $faker) {
            $name = $faker->realText(mt_rand(11, 25));
            $product->translateOrNew($locale)->name = $name;
            $product->translateOrNew($locale)->slug = str($name)->slug(language: $locale);
            $product->translateOrNew($locale)->description = $faker->realText(120);
        }

        $product->save();
    }

    /**
     * с 90% шансом у продукта будет атрибут (пока у нас только Цвет и Размер),
     * и с 20% вероятностью 1 из опций атрибутов кроме Rang не добавится к sku,
     * тем самым мы добъемся наличия:
     * 1) только Rang (20%);
     * 2) Rang и O'lcham (100-20=80%);
     * 3) никаких атрибутов (100-90=10%);
     * для sku
     *
     * @param Product $product
     * @return void
     */
    private function createProductVariants(Product $product): void
    {
        $attributes = Attribute::with('options')->get();

        foreach (range(1, mt_rand(1, 3)) as $value) {
            $createdSku = $this->createProductSku($product);
            $attributes->each(function (Attribute $attribute) use ($product, $createdSku) {
                if (mt_rand(1, 10) <= 90) {
                    $option = $attribute->options->random();

                    if ($attribute->id > 1 && mt_rand(1, 10) <= 2) {
                        return;
                    }
                    // прикрепляем изображения
                    $textOnImage = sprintf(
                        '%s %s',
                        $product->name,
                        $option->value
                    );
                    $this->addImageToProductSku($createdSku, $textOnImage, 2);

                    // прикрепляем варианты
                    $createdSku->attributeOptions()->attach($option);
                } else {
                    $this->addImageToProductSku($createdSku, $product->name);
                }
            });
        }
    }

    private function createProductSku(Product $product): Sku
    {
        $price = fake()->numberBetween(100_000, 600_000);

        return $product->skus()->create([
            'sku' => str()->random(11),
            'price' => (int)round($price, -3),
        ]);
    }

    /**
     * Рандомно добавляет от 1 до `$n` количество изображений
     * к `$sku`, с текстом на картинке в виде `$textOnImage`.
     * К `$textOnImage` в конце добавляем
     * порядковый номер изображения от 1 до `$n` с эмоджи
     *
     * @param Sku $sku
     * @param string $textOnImage
     * @param int $n
     * @return void
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    private function addImageToProductSku(
        Sku $sku,
        string $textOnImage,
        int $n = 3
    ): void {
        foreach (range(1, mt_rand(1, $n)) as $value) {
            [$imageName, $imageText] = $this->getProductSkuImageTexts(
                $textOnImage,
                $value,
            );
            $fakeImageUrl = $this->prepareFakeImgUrl($imageText);

            $sku
                ->addMediaFromUrl($fakeImageUrl)
                ->usingName($imageName)
                ->usingFileName(str($imageName)->slug() . '.png')
                ->toMediaCollection();
        }
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
        $customFontSize = '&font_size=70';
        $imageUrlWithoutFontSize = $this->faker->fakeImgUrl(
            width: 800,
            height: 1200,
            text: $text
        );
        return sprintf(
            '%s%s',
            $imageUrlWithoutFontSize,
            $customFontSize,
        );
    }

    private function getProductSkuImageTexts(string $text, int $ordinal): array
    {
        $imageName = sprintf(
            '%s %s',
            $text,
            $ordinal,
        );

        $discordEmotes = collect(
            array_map(
                static fn($emote) => $emote->value,
                DiscordEmotes::cases()
            )
        );
        $imageText = sprintf(
            '%s %s %s',
            $text,
            $ordinal,
            $discordEmotes->random()
        );

        return [
            $imageName,
            $imageText,
        ];
    }
}
