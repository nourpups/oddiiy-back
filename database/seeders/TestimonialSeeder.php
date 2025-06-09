<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonial = [
            'author_name' => 'Quvondiq Qutbiddinov',
            'uz' => [
                'title' => 'Raqsga tushar Shaxnozalar, Shaxnooozalar',
                'text' => "Xo'b ajoyib, yurtimiz bor, yarmi paxta, yarmi bol.
                        Yarmi yozu, yarmi bahor, yarmi jannat, yarmi tog'.
                        Yarmi gulshan, yarmi bog, Zulfizarlar avji dilbar, yarmi gulgun, yarmi mox"
            ],
            'ru' => [
                'title' => 'Лоне Вулф',
                'text' => "Помни брат, а то забудешь.
                        Если упал, то встань, если встал, упай. если упай, чокопай.
                        Знаешь солнечный удар? я научил солнце так бить"
            ],
        ];

        foreach (range(1, 4) as $value) {
            Testimonial::query()->create($testimonial);
        }
    }
}
