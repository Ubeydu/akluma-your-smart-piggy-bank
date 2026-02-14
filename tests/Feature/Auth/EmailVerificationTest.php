<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/en/verify-email');

    $response->assertStatus(200);
    $response->assertSee($user->email);
    $response->assertSee('Check your spam or junk folder', false);
    $response->assertSee('contact@akluma.com');
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'localized.verification.verify.en',
        now()->addMinutes(60),
        ['locale' => 'en', 'id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect('/en/dashboard?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'localized.verification.verify.en',
        now()->addMinutes(60),
        ['locale' => 'en', 'id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verify email screen shows wrong email link', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/en/verify-email');

    $response->assertSuccessful();
    $response->assertSee('Wrong email?', false);
});

test('unverified user can update email and resend verification', function () {
    $user = User::factory()->unverified()->create(['email' => 'old@example.com']);

    $response = $this->actingAs($user)->patch('/en/email/update-unverified', [
        'email' => 'new@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'verification-link-sent');

    $user->refresh();
    expect($user->email)->toBe('new@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('update email requires valid email format', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->patch('/en/email/update-unverified', [
        'email' => 'not-an-email',
    ]);

    $response->assertSessionHasErrors('email');
});

test('update email rejects email without valid domain', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->patch('/en/email/update-unverified', [
        'email' => 'user@gmail',
    ]);

    $response->assertSessionHasErrors('email');
});

test('update email rejects already taken email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->patch('/en/email/update-unverified', [
        'email' => 'taken@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

test('verified user cannot update email via verification endpoint', function () {
    $user = User::factory()->create(); // verified by default

    $response = $this->actingAs($user)->patch('/en/email/update-unverified', [
        'email' => 'new@example.com',
    ]);

    $response->assertForbidden();
});

test('guest cannot update email via verification endpoint', function () {
    $response = $this->patch('/en/email/update-unverified', [
        'email' => 'new@example.com',
    ]);

    $response->assertRedirect();
});

test('resend verification email returns cooldown on success', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->post('/en/email/verification-notification');

    $response->assertRedirect();
    $response->assertSessionHas('status', 'verification-link-sent');
    $response->assertSessionHas('cooldown', 120);
});

test('resend verification email is rate limited to one attempt per two minutes', function () {
    $user = User::factory()->unverified()->create();

    // First attempt should succeed
    $response = $this->actingAs($user)->post('/en/email/verification-notification');
    $response->assertSessionHas('status', 'verification-link-sent');

    // Second attempt should be rate limited
    $response = $this->actingAs($user)->post('/en/email/verification-notification');
    $response->assertRedirect();
    $response->assertSessionHas('cooldown');
    $response->assertSessionMissing('status');

    expect(session('cooldown'))->toBeGreaterThan(0)->toBeLessThanOrEqual(120);
});

test('resend verification email is available again after cooldown expires', function () {
    $user = User::factory()->unverified()->create();

    // First attempt
    $this->actingAs($user)->post('/en/email/verification-notification');

    // Clear the rate limiter to simulate time passing
    RateLimiter::clear('verify-email:'.$user->id);

    // Should succeed again
    $response = $this->actingAs($user)->post('/en/email/verification-notification');
    $response->assertSessionHas('status', 'verification-link-sent');
});

test('update email shares cooldown with resend button', function () {
    $user = User::factory()->unverified()->create(['email' => 'old@example.com']);

    // Resend triggers cooldown
    $this->actingAs($user)->post('/en/email/verification-notification');

    // Update email should be blocked by the same cooldown
    $response = $this->actingAs($user)->patch('/en/email/update-unverified', [
        'email' => 'new@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('cooldown');
    $response->assertSessionMissing('status');

    // Email should NOT have changed
    expect($user->fresh()->email)->toBe('old@example.com');
});
