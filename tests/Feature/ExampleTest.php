<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    $user = \App\Models\User::factory()->create();
    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect(route('dashboard'));
});
