<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubResource;
use App\Models\Club;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClubController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $clubs = Club::withCount(['events' => fn (Builder $query) => $query->published()])
            ->orderBy('name')
            ->get();

        return ClubResource::collection($clubs);
    }

    public function show(Club $club): ClubResource
    {
        return new ClubResource(
            $club->loadCount(['events' => fn (Builder $query) => $query->published()])
        );
    }
}
