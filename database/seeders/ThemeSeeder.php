<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            'Intelligence Artificielle',
            'Développement Web',
            'Entrepreneuriat',
            'Design & UX',
            'Cybersécurité',
            'Culture & Art',
        ];

        foreach ($themes as $name) {
            Theme::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
