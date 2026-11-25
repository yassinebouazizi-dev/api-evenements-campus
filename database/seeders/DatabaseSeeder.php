<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Event;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory(2)->create();

        $this->call([
            ClubSeeder::class,
            ThemeSeeder::class,
        ]);

        $users = User::all();
        $clubs = Club::all();
        $themes = Theme::all();

        $ownerAndClub = fn (array $attributes) => [
            'user_id' => $users->random()->id,
            'club_id' => $clubs->random()->id,
        ];

        Event::factory(18)->published()->state($ownerAndClub)->create();
        Event::factory(4)->state($ownerAndClub)->create();

        Event::all()->each(function (Event $event) use ($themes) {
            $event->themes()->attach($themes->random(2));
        });
    }
}
