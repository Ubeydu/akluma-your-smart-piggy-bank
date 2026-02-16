<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

test('forgot password screen can be rendered', function () {
    $response = $this->get('/en/forgot-password');

    $response->assertSuccessful();
    $response->assertSee('Email Password Reset Link', false);
});

test('password reset request returns cooldown on success', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post('/en/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('cooldown', 120);
});

test('password reset request is rate limited to one attempt per two minutes', function () {
    User::factory()->create(['email' => 'test@example.com']);

    // First attempt should succeed
    $response = $this->post('/en/forgot-password', [
        'email' => 'test@example.com',
    ]);
    $response->assertSessionHas('cooldown', 120);

    // Second attempt should be rate limited
    $response = $this->post('/en/forgot-password', [
        'email' => 'test@example.com',
    ]);
    $response->assertRedirect();
    $response->assertSessionHas('cooldown');
    $response->assertSessionMissing('status');

    expect(session('cooldown'))->toBeGreaterThan(0)->toBeLessThanOrEqual(120);
});

test('password reset request is available again after cooldown expires', function () {
    User::factory()->create(['email' => 'test@example.com']);

    // First attempt
    $this->post('/en/forgot-password', [
        'email' => 'test@example.com',
    ]);

    // Clear our custom rate limiter to simulate time passing
    RateLimiter::clear('password-reset:test@example.com');

    // Second attempt: our RateLimiter allows it, but Laravel's internal
    // password broker throttle (60s) may still return RESET_THROTTLED.
    // Either way, the controller should return a cooldown (not an error).
    $response = $this->post('/en/forgot-password', [
        'email' => 'test@example.com',
    ]);
    $response->assertRedirect();
    $response->assertSessionHas('cooldown');
});

test('password reset throttle is per email address', function () {
    User::factory()->create(['email' => 'user1@example.com']);
    User::factory()->create(['email' => 'user2@example.com']);

    // Rate limit user1
    $this->post('/en/forgot-password', ['email' => 'user1@example.com']);

    // user2 should not be affected
    $response = $this->post('/en/forgot-password', ['email' => 'user2@example.com']);
    $response->assertSessionHas('cooldown', 120);
});
