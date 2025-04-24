<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addressData = [
            'formatted' => 'Ташкент, улица Махорат, 76',
            'city' => 'Ташкент',
            'street' => 'Махорат',
            'house' => '76',
            'orientation' => 'Birlik krug, Mirbobo ota masjidi',
        ];

        $user = User::query()->first();
        $user->address()->create($addressData);
    }
}
