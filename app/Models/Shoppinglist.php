<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shoppinglist extends Model
{
    protected $fillable = ['name', 'household_id', 'user_id'];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Shoppinglist $shoppinglist) {
            $shoppinglist->items()->delete();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
