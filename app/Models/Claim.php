<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Claim extends Model {
    protected $fillable = ['item_id','claimant_id','claimant_name','student_id','proof','status','response_note'];
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function claimant(): BelongsTo { return $this->belongsTo(User::class, 'claimant_id'); }
}
