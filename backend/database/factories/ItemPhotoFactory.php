<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ItemPhoto> */
class ItemPhotoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'disk' => 'local',
            'path' => 'item-photos/'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => fake()->numberBetween(1024, 65536),
            'sort_order' => 0,
        ];
    }
}
