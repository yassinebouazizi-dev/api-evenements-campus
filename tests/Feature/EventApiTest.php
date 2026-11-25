<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\Theme;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function eventPayload(int $clubId, array $overrides = []): array
{
    return array_merge([
        'club_id' => $clubId,
        'title' => 'Hackathon IA Campus',
        'slug' => 'hackathon-ia-campus',
        'summary' => 'Un hackathon de 24h autour de l intelligence artificielle.',
        'description' => 'Description detaillee de l evenement organise sur le campus.',
        'location' => 'Amphi A',
        'max_seats' => 80,
        'starts_at' => '2026-12-10 09:00:00',
        'is_published' => true,
    ], $overrides);
}

it('lists published events with the expected JSON structure', function () {
    Event::factory()->published()->count(2)->create();
    Event::factory()->create(['title' => 'Brouillon prive']);

    $this->getJson('/api/events')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => ['*' => [
                'id',
                'title',
                'slug',
                'summary',
                'description',
                'location',
                'max_seats',
                'starts_at',
                'is_published',
                'organizer',
                'club',
                'themes',
            ]],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('filters events by club slug', function () {
    $club = Club::factory()->create();
    $matching = Event::factory()->published()->create(['club_id' => $club->id, 'title' => 'Atelier Robotique']);
    Event::factory()->published()->create(['title' => 'Autre Evenement']);

    $this->getJson('/api/events?club='.$club->slug)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->id)
        ->assertJsonPath('data.0.club.slug', $club->slug);
});

it('lists clubs with published events count', function () {
    $club = Club::factory()->create();
    Event::factory()->published()->count(2)->create(['club_id' => $club->id]);
    Event::factory()->create(['club_id' => $club->id]);

    $this->getJson('/api/clubs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $club->slug)
        ->assertJsonPath('data.0.events_count', 2);
});

it('refuses to create an event without a token', function () {
    $club = Club::factory()->create();

    $this->postJson('/api/events', eventPayload($club->id))
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');

    $this->assertDatabaseCount('events', 0);
});

it('forbids updating an event owned by another user before validation', function () {
    $event = Event::factory()->create(['user_id' => User::factory()->create()->id]);
    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/events/'.$event->id, ['title' => ''])
        ->assertForbidden();
});

it('rejects an invalid event with French validation messages', function () {
    $club = Club::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/events', eventPayload($club->id, [
        'title' => '',
        'max_seats' => 2,
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'max_seats'])
        ->assertJsonPath('errors.title.0', 'Le titre est obligatoire.');
});

it('creates an event and syncs its themes in database', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create();
    $themes = Theme::factory()->count(2)->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/events', eventPayload($club->id, [
        'themes' => $themes->pluck('id')->all(),
    ]))
        ->assertCreated()
        ->assertJsonPath('data.slug', 'hackathon-ia-campus')
        ->assertJsonPath('data.organizer', $user->name)
        ->assertJsonCount(2, 'data.themes');

    $eventId = $response->json('data.id');

    $this->assertDatabaseHas('events', [
        'id' => $eventId,
        'user_id' => $user->id,
        'club_id' => $club->id,
        'slug' => 'hackathon-ia-campus',
    ]);

    foreach ($themes as $theme) {
        $this->assertDatabaseHas('event_theme', [
            'event_id' => $eventId,
            'theme_id' => $theme->id,
        ]);
    }
});

it('lets the owner delete their event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);
    Sanctum::actingAs($user);

    $this->deleteJson('/api/events/'.$event->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('events', ['id' => $event->id]);
});
