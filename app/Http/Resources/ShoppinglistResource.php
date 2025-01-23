<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShoppinglistResource extends JsonResource
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
            'name' => $this->name,
            'household' => new HouseholdResource($this->household),
            'user' => new UserMinimalResource($this->user),
            'items' => ItemMinimalResource::collection($this->items),
        ];
    }
}
