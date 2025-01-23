<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class HouseholdPolicy
{
    public function update(User $user, Household $household): Response
    {
        return $user->isOwner($household)
            ? Response::allow()
            : Response::deny('You are not the owner of this household');
    }

    public function view(Household $household, User $user): Response
    {
        return $household->users->contains($user) || $household->owner_id === $user->id
            ? Response::allow()
            : Response::deny('You are not a member of this household');
    }
}
