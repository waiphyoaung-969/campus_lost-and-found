<?php

namespace App\Models;

use Database\Factories\ItemPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPhoto extends Model
{
    /** @use HasFactory<ItemPhotoFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $hidden = ['disk', 'path'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Item, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
