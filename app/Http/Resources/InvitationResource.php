<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'household' => new HouseholdResource($this->household),
            'invited_user' => new UserMinimalResource($this->invitedUser),
            'inviter_user' => new UserMinimalResource($this->inviterUser),
            'status' => $this->status,
        ];
    }
}
