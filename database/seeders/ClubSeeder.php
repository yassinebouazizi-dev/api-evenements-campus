<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = [
            ['name' => 'Club Robotique', 'description' => 'Ateliers robotique, IoT et systèmes embarqués.'],
            ['name' => 'Club Enactus', 'description' => 'Entrepreneuriat social et projets étudiants.'],
            ['name' => 'Club Google Developer Group', 'description' => 'Conférences et ateliers développement web, mobile et IA.'],
            ['name' => 'Club Théâtre & Arts', 'description' => 'Activités culturelles, théâtre et projections sur le campus.'],
        ];

        foreach ($clubs as $club) {
            Club::create([
                'name' => $club['name'],
                'slug' => Str::slug($club['name']),
                'description' => $club['description'],
            ]);
        }
    }
}
