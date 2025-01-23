<?php

namespace App\Http\Controllers;

use App\Http\Resources\HouseholdRequestResource;
use App\Http\Resources\HouseholdResource;
use App\Http\Resources\InvitationResource;
use App\Models\Household;
use App\Models\HouseholdRequest;
use App\Models\Invitation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class HouseholdController extends Controller
{
    public function index()
    {
        $households = Auth::user()->households->merge(Auth::user()->ownedHouseholds)->unique();
        //return response()->json($households);
        return HouseholdResource::collection($households);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $household = Household::create([
            'name' => $request->name,
            'owner_id' => Auth::id(),
        ]);

        return new HouseholdResource($household);
    }

    //public function invite(Request $request, Household $household)
    //{
    //    try {
    //        Gate::authorize('update', $household);
    //    } catch (AuthorizationException $e) {
    //        Log::channel('custom')->warning('Unauthorized invite detected', [
    //            'user_id' => Auth::id(),
    //            'household_id' => $household->id,
    //            'time' => date('d.m.Y H:i:s', now()->timestamp)
    //        ]);
    //        return response()->json(['message' => 'You are not the owner of this household'], 403);
    //    }
    //    $request->validate(['email' => 'required|email']);
//
    //    $user = User::where('email', $request->email)->first();
    //    if (!$user) {
    //        return response()->json(['message' => 'User not found'], 404);
    //    }
//
    //    $household->users()->attach($user->id);
    //    return response()->json(['message' => 'User invited']);
    //}

    public function leave(Household $household)
    {
        $household->users()->detach(Auth::id());
        return response()->json(['message' => 'User left household']);
    }

    public function removeUser(Household $household, User $user)
    {
        try {
            Gate::authorize('update', $household);
        } catch (AuthorizationException $e) {
            Log::channel('custom')->warning('Unauthorized remove user detected', [
                'user_id' => Auth::id(),
                'household_id' => $household->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp),
                'message' => $e
            ]);
            return response()->json(['message' => 'You are not the owner of this household'], 403);
        }
        $household->users()->detach($user->id);
        return response()->json(['message' => 'User removed from household']);
    }

    public function update(Request $request, Household $household)
    {
        try {
            Gate::authorize('update', $household);
        } catch (AuthorizationException $e) {
            Log::channel('custom')->warning('Unauthorized update detected', [
                'user_id' => Auth::id(),
                'household_id' => $household->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            return response()->json(['message' => 'You are not the owner of this household'], 403);
        }
        $request->validate(['name' => 'required|string|max:255']);
        $household->update($request->all());
        return new HouseholdResource($household);
    }

    public function destroy(Household $household)
    {
        try {
            Gate::authorize('update', $household);
        } catch (AuthorizationException $e) {
            Log::channel('custom')->warning('Unauthorized delete detected', [
                'user_id' => Auth::id(),
                'household_id' => $household->id,
                'time' => date('d.m.Y H:i:s', now()->timestamp)
            ]);
            return response()->json(['message' => 'You are not the owner of this household'], 403);
        }
        $household->delete();
        return response()->json(['message' => 'Household deleted']);
    }

    public function inviteUser(Request $request, Household $household)
    {
        $request->validate(['user_code' => 'required|exists:users,code']);
        $invitedUser = User::where('code', $request->user_code)->first();

        if (Invitation::where('household_id', $household->id)
            ->where('invited_user_id', $invitedUser->id)
            ->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'User already invited'], 409);
        }

        $invitation = Invitation::create([
            'household_id' => $household->id,
            'invited_user_id' => $invitedUser->id,
            'inviter_user_id' => Auth::id(),
        ]);

        return new InvitationResource($invitation);
    }

    public function showInvitations()
    {
        $invitations = Auth::user()->receivedInvitations()
            ->with(['household', 'inviterUser'])->get();
        return InvitationResource::collection($invitations);
    }

    public function respondToInvitation(Request $request, Invitation $invitation)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined'
        ]);
        if ($invitation->invited_user_id !== Auth::id()) {
            return response()->json(['message' => 'You are not the invited user'], 403);
        }

        $invitation->update([
            'status' => $request->status
        ]);

        if ($request->status === 'accepted') {
            $household = Household::find($invitation->household_id);
            $household->users()->attach(Auth::id());
        }

        return new InvitationResource($invitation);
    }

    public function getPublicUserHouseholds(Request $request)
    {
        $request->validate(['user_code' => 'required|exists:users,code']);

        $user = User::where('code', $request->user_code)->first();
        $households = $user->ownedHouseholds->where('is_private', false)->get();

        return HouseholdResource::collection($households);
    }

    public function requestToJoin(Request $request, Household $household)
    {
        if ($household->is_private) {
            return response()->json(['message' => 'Household is private'], 403);
        }

        if ($household->users()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'You are already a member of this household'], 409);
        }

        if (HouseholdRequest::where('household_id', $household->id)
            ->where('requesting_user_id', Auth::id())
            ->where('status', 'pending')
            ->exists()) {
            return response()->json(['message' => 'Request already pending'], 409);
        }

        $householdRequest = HouseholdRequest::create([
            'household_id' => $household->id,
            'requesting_user_id' => Auth::id(),
        ]);

        return new HouseholdRequestResource($householdRequest);
    }

    public function showAllJoinRequests()
    {
        $requests = Auth::user()->households()->with('householdRequests.requestingUser')->get();
        return HouseholdRequestResource::collection($requests);
    }

    public function showHouseholdJoinRequests(Household $household)
    {
        $requests = $household->householdRequests()->with('requestingUser')->get();
        return HouseholdRequestResource::collection($requests);
    }

    public function respondToJoinRequests(Request $request, HouseholdRequest $householdRequest)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined'
        ]);

        if ($householdRequest->household->owner_id !== Auth::id()) {
            return response()->json(['message' => 'You are not the owner of this household'], 403);
        }

        $householdRequest->update([
            'status' => $request->status
        ]);

        if ($request->status === 'accepted') {
            $householdRequest->household->users()->attach($householdRequest->requesting_user_id);
        }

        return new HouseholdRequestResource($householdRequest);
    }
}
