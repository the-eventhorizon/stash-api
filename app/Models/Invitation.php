<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'household_id',
        'invited_user_id',
        'inviter_user_id',
        'status',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function inviterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }
}
