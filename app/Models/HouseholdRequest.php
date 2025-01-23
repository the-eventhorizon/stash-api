<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdRequest extends Model
{
    protected $fillable = [
        'household_id',
        'requesting_user_id',
        'status',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function requestingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_user_id');
    }
}
