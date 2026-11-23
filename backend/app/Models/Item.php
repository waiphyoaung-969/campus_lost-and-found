<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'location_id',
        'type',
        'name',
        'description',
        'date_seen',
        'holding_location',
    ];

    /** @var list<string> */
    protected $hidden = ['description', 'holding_location', 'claims', 'photos'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date_seen' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** @return HasMany<Claim, $this> */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /** @return HasMany<ItemPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(ItemPhoto::class);
    }

    /** @param Builder<Item> $query */
    #[Scope]
    protected function forPublicSearch(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), 'open')
            ->where($this->qualifyColumn('created_at'), '>=', now()->subDays(60));
    }
}
