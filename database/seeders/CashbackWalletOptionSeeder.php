<?php

namespace Database\Seeders;

use App\Models\CashbackWalletOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashbackWalletOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $optionValues = [10_000, 20_000, 35_000, 50_000, 75_000, 100_000];
        $options = array_map(static fn($v) => [
            'value' => $v,
            'created_at' => now()
        ], $optionValues);

        CashbackWalletOption::query()->upsert($options, 'id');
    }
}
