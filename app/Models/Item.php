<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Item extends Model {
    protected $fillable = ['user_id','type','name','category','description','date_seen','location','holding_location','photos','status'];
    protected $casts = ['date_seen' => 'date', 'photos' => 'array'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function claims(): HasMany { return $this->hasMany(Claim::class); }
}
