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
use Illuminate\Database\Seeder;
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
        Category::all()->map(function (Category $category) {
            Product::factory()
                ->count(mt_rand(2, 8))
                ->for($category)
                ->create()
                ->map(function (Product $product) {
                    // создаём переводы
                    $this->createProductTranslations($product);

                    // создаём sku
                    $this->createProductVariants($product);

                });
        });

        $this->createDiscountForCategoryWithMostProducts();
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
                        $this->addImagesToProductSku($createdSku, $textOnImage, 2);

                        // прикрепляем варианты
                        $createdSku->attributeOptions()->attach($attributeOption);
                    });
            } else {
                $createdSku = $this->createProductSku($product);

                $this->addImagesToProductSku($createdSku, $product->name);
            }
        });
    }

    private function createProductSku(Product $product): Sku
    {
        $price = fake()->numberBetween(100_000, 600_000);

        return $product->skus()->create([
            'sku' => str()->random(10),
            'price' => (int)round($price, -2),
        ]);
    }

    /**
     * Даём скидку 50% категории (продуктам с этой категорией)
     * c наибольшим количеством продуктов, запрос делается здесь
     * тк. для выполнения запроса с фильтрами нужны продукты,
     * которые только сейчас и создались
     *
     * @return void
     */
    private function createDiscountForCategoryWithMostProducts(): void
    {
        $latestCategoryWithMostProducts = Category::query()
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->latest()
            ->first();

        $latestCategoryWithMostProducts->discount()->create([
            'value' => 50,
            'type' => SaleType::FIXED,
            'starts_at' => now(),
            'expires_at' => now()->addDays(21)
        ]);
    }

    private function createProductTranslations($product): void
    {
        $fakers = [
            Locale::RU->value => Factory::create('ru_RU'),
            Locale::UZ->value => fake(),
        ];

        /** @var \Faker\Generator $faker */
        foreach ($fakers as $locale => $faker) {
            $name = $faker->unique()->words(mt_rand(1, 3), true);
            $product->translateOrNew($locale)->name = $name;
            $product->translateOrNew($locale)->slug = str()->slug($name, $locale);
            $product->translateOrNew($locale)->description = $faker->realText(120);
        }

        $product->save();
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
    private function addImagesToProductSku(
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
                ->usingFileName($imageName . '.png')
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
