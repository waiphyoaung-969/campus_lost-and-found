<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Item> */
class ItemFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->microsoft(),
            'category_id' => Category::factory(),
            'location_id' => Location::factory(),
            'type' => 'lost',
            'name' => ucfirst(fake()->words(3, true)),
            'description' => fake()->sentence(),
            'date_seen' => today()->subDays(fake()->numberBetween(0, 14)),
            'status' => 'open',
        ];
    }

    public function found(): static
    {
        return $this->state(fn (): array => [
            'type' => 'found',
            'holding_location' => 'Campus lost and found desk',
        ]);
    }
}
