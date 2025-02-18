<?php

namespace Database\Seeders;

use App\Models\AttributeOption;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'name' => 'OddiiyUser',
            'phone' => '+998901234567',
            'password' => Hash::make('oddiiy'),
        ]);

        User::factory()->create([
            'name' => 'IScuf',
            'phone' => '+998800858166',
            'password' => Hash::make('oddiiy'),
        ]);

        $this->call([
            AttributeSeeder::class,
            CategorySeeder::class,
            CouponSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
