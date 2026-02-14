<?php

use App\Models\User;

test('unverified user can access dashboard', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/en/dashboard');

    $response->assertSuccessful();
});

test('unverified user can access piggy banks index', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/en/piggy-banks');

    $response->assertSuccessful();
});

test('unverified user can access vaults index', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/en/vaults');

    $response->assertSuccessful();
});

test('unverified user can access profile', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/en/profile');

    $response->assertSuccessful();
});

test('unverified user can access draft piggy banks', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/en/draft-piggy-banks');

    $response->assertSuccessful();
});

test('verified user does not see verification banner on dashboard', function () {
    $user = User::factory()->create(); // verified by default

    $response = $this->actingAs($user)->get('/en/dashboard');

    $response->assertSuccessful();
    $response->assertDontSee('Verify your email to make sure your savings reminders reach you.');
});

test('unverified user sees verification banner on dashboard', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/en/dashboard');

    $response->assertSuccessful();
    $response->assertSee('Verify your email to make sure your savings reminders reach you.');
});

test('registration redirects to dashboard instead of verification notice', function () {
    $response = $this->post('/en/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
        'privacy' => true,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/en/dashboard');
});

test('unverified user can perform write actions', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->patch('/en/profile', [
        'name' => 'Updated Name',
        'email' => $user->email,
    ]);

    $response->assertRedirect();
});

test('verification flow still works for unverified user', function () {
    $user = User::factory()->unverified()->create();

    expect($user->hasVerifiedEmail())->toBeFalse();

    // Verify the email
    $user->markEmailAsVerified();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
