<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'OoddiiyUser',
            'phone' => '+998901234567',
            'password' => Hash::make('ooddiiy'),
        ]);

        User::factory()->create([
            'name' => 'IScuf',
            'phone' => '+998800858166',
            'password' => Hash::make('ooddiiy'),
        ]);

        $this->call([
            AttributeSeeder::class,
            CategorySeeder::class,
            CouponSeeder::class,
            ProductSeeder::class,
            CollectionSeeder::class,
            TagSeeder::class,
            DiscountSeeder::class,
            AddressSeeder::class,
        ]);
    }
}
