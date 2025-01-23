<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    protected $fillable = ['name', 'owner_id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shoppinglists(): HasMany
    {
        return $this->hasMany(Shoppinglist::class);
    }

    public function householdRequests(): HasMany
    {
        return $this->hasMany(HouseholdRequest::class, 'household_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'household_id');
    }
}
