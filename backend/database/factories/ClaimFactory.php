<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Claim> */
class ClaimFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory()->found(),
            'claimant_id' => User::factory()->microsoft(),
            'claimant_name' => fake()->name(),
            'proof' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
