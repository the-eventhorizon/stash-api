<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = ['name', 'shoppinglist_id', 'user_id', 'checked'];

    public function shoppinglist(): BelongsTo
    {
        return $this->belongsTo(Shoppinglist::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
