<?php

namespace Database\Seeders;

use App\Enum\Locale;
use App\Enum\SaleType;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sku;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                    ->count(mt_rand(1, 2))
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
     * с 70% шансом у продукта будет атрибут (пока  у нас только Цвет),
     * и от 1 до 3 "вариантов" этого атрибута (Белый, Черный, Голубой)
     *
     * вариантов 3, тк. в AttributeSeeder столько вариантов
     * и создаётся, учти для будущих изменений
     *
     * @param Product $product
     * @return void
     */
    private function createProductVariants(Product $product): void
    {
        $attributes = Attribute::with('attributeOptions')->get();

        $attributes->map(function (Attribute $attribute) use ($product) {
            if (mt_rand(1, 10) <= 7) {
                $attribute->attributeOptions
                    ->random(mt_rand(1, 3))
                    ->map(function (AttributeOption $attributeOption) use ($product) {
                        $createdSku = $this->createProductSku($product);

                        // прикрепляем изображения
                        $textOnImage = sprintf(
                            '%s %s',
                            $product->name,
                            $attributeOption->value
                        );
                        $this->addImageToProductSku($createdSku, $textOnImage, 2);

                        // прикрепляем варианты
                        $createdSku->attributeOptions()->attach($attributeOption);
                    });
            } else {
                $createdSku = $this->createProductSku($product);

                $this->addImageToProductSku($createdSku, $product->name);
            }
        });
    }

    private function createProductSku(Product $product): Sku
    {
        $price = fake()->numberBetween(100_000, 600_000);

        return $product->skus()->create([
            'sku' => str()->random(10),
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

        $discordEmotes = collect([
            '<:Pepe:1341123333529403393>',
            '<:pepesip:1341124093524578305>',
            '<:Welcome:1341125555482787841>',
            '<:slavic_pepe:1341125889030750370>',
        ]);
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
