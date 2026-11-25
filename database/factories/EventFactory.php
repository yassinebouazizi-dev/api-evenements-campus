<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'user_id' => User::factory(),
            'club_id' => Club::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(10),
            'description' => fake()->paragraphs(3, true),
            'location' => fake()->randomElement(['Amphi A', 'Amphi B', 'Salle B12', 'Hall Principal', 'Labo Info 2']),
            'max_seats' => fake()->numberBetween(20, 200),
            'starts_at' => fake()->dateTimeBetween('+2 days', '+2 months'),
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }
}
