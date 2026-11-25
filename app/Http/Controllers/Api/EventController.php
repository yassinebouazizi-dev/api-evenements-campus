<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function index(IndexEventRequest $request): AnonymousResourceCollection
    {
        $perPage = (int) ($request->validated('per_page') ?? 10);

        $events = Event::published()
            ->with('user', 'club', 'themes')
            ->when($request->validated('club'), fn (Builder $query, string $slug) => $query->whereRelation('club', 'slug', $slug))
            ->when($request->validated('theme'), fn (Builder $query, string $slug) => $query->whereRelation('themes', 'slug', $slug))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return EventResource::collection($events);
    }

    public function show(Event $event): EventResource
    {
        return new EventResource($event->load('user', 'club', 'themes'));
    }
}
