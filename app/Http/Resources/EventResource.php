<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
            'location' => $this->location,
            'max_seats' => $this->max_seats,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'is_published' => $this->is_published,
            'organizer' => $this->whenLoaded('user', fn () => $this->user->name),
            'club' => new ClubResource($this->whenLoaded('club')),
            'themes' => ThemeResource::collection($this->whenLoaded('themes')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
