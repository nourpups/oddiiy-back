<?php

namespace App\Providers;

use Faker\Factory;
use Faker\Generator;
use FakerRestaurant\Provider\en_US\Restaurant;
use Illuminate\Support\ServiceProvider;
use Mmo\Faker\PicsumProvider;
use Mmo\Faker\LoremSpaceProvider;
use Mmo\Faker\FakeimgProvider;

class FakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $locale = config('app.faker_locale');

        // регается чтобы работало $this->faker
        $abstract = Generator::class;

        $this->app->singleton($abstract, function () use ($locale) {
            $faker = Factory::create($locale);

            // fakeimg пока хватает, не удаляю чтобы
            // не забывал что этими тоже можно пользоваться
            // $faker->addProvider(new PicsumProvider($faker));
            // $faker->addProvider(new LoremSpaceProvider($faker));
            $faker->addProvider(new FakeimgProvider($faker));

            return $faker;
        });

        // регается чтобы работал хелпер fake()
        // так как fake() добавляется в контейнер с postfix'ом в виде локаля. (чекни fake() ctrl+click'ом)
        $abstract .= ':' . $locale;

        $this->app->singleton($abstract, function () use ($locale) {
            $faker = Factory::create($locale);

            // fakeimg пока хватает, не удаляю чтобы
            // не забывал что этими тоже можно пользоваться
            // $faker->addProvider(new PicsumProvider($faker));
            // $faker->addProvider(new LoremSpaceProvider($faker));
            $faker->addProvider(new FakeimgProvider($faker));

            return $faker;
        });

        // чекни: https://laracasts.com/discuss/channels/laravel/unable-to-seed-fake-image-laravel

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
