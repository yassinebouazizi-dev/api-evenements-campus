<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

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

    public function store(StoreEventRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['is_published'] = $request->boolean('is_published');

        $event = $request->user()->events()->create(Arr::except($validated, ['themes']));
        $event->themes()->sync($validated['themes'] ?? []);

        return (new EventResource($event->load('user', 'club', 'themes')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Event $event): EventResource
    {
        return new EventResource($event->load('user', 'club', 'themes'));
    }

    public function update(UpdateEventRequest $request, Event $event): EventResource
    {
        $validated = $request->validated();
        $validated['is_published'] = $request->boolean('is_published');

        $event->update(Arr::except($validated, ['themes']));
        $event->themes()->sync($validated['themes'] ?? []);

        return new EventResource($event->load('user', 'club', 'themes'));
    }

    public function destroy(Event $event): Response
    {
        Gate::authorize('delete', $event);
        $event->delete();

        return response()->noContent();
    }
}
