<?php

namespace Database\Seeders;

use App\Models\Font;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FontSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fonts = [
            [
                'is_selected' => true,
                'name' => 'airtemoeG',
                'class' => 'airtemoeg'
            ],
            [
                'is_selected' => false,
                'name' => 'Agrandir',
                'class' => 'agrandir'
            ],
            [
                'is_selected' => false,
                'name' => 'BwQuintaPro',
                'class' => 'bw-quinta-pro'
            ],
        ];

        Font::query()->upsert($fonts, 'id');
    }
}
