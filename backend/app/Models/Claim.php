<?php

namespace App\Models;

use Database\Factories\ClaimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
    /** @use HasFactory<ClaimFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['claimant_name', 'proof'];

    /** @var list<string> */
    protected $hidden = ['claimant_id', 'claimant_name', 'proof', 'response_note', 'claimant'];

    /** @return BelongsTo<Item, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /** @return BelongsTo<User, $this> */
    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }
}
