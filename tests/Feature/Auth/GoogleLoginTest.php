<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('google redirect route sends user to google', function () {
    $response = $this->get(route('auth.google.redirect', ['timezone' => 'Europe/Istanbul', 'language' => 'tr']));

    $response->assertRedirect();
    $response->assertRedirectContains('accounts.google.com');
});

test('google callback creates a new user and logs them in', function () {
    $googleUser = new SocialiteUser;
    $googleUser->id = '123456789';
    $googleUser->name = 'Jane Doe';
    $googleUser->email = 'jane@example.com';
    $googleUser->map(['id' => '123456789', 'name' => 'Jane Doe', 'email' => 'jane@example.com']);

    $provider = $this->mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->withSession([
        'google_timezone' => 'Europe/Istanbul',
        'google_language' => 'tr',
    ])->get(route('auth.google.callback'));

    $response->assertRedirect();

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'google_id' => '123456789',
        'language' => 'tr',
        'timezone' => 'Europe/Istanbul',
    ]);

    $user = User::where('email', 'jane@example.com')->first();
    expect($user->accepted_terms_at)->not->toBeNull();
    expect($user->accepted_privacy_at)->not->toBeNull();
    expect($user->getAttributes()['password'])->toBeNull();
    expect($user->hasPassword())->toBeFalse();
});

test('google callback logs in existing user who registered with email', function () {
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'google_id' => null,
        'email_verified_at' => null,
    ]);

    $googleUser = new SocialiteUser;
    $googleUser->id = '987654321';
    $googleUser->name = 'Existing User';
    $googleUser->email = 'existing@example.com';
    $googleUser->map(['id' => '987654321', 'name' => 'Existing User', 'email' => 'existing@example.com']);

    $provider = $this->mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect();
    $this->assertAuthenticatedAs($existingUser);

    $existingUser->refresh();
    expect($existingUser->google_id)->toBe('987654321');
    expect($existingUser->email_verified_at)->not->toBeNull();
});

test('google callback does not overwrite existing google_id', function () {
    $existingUser = User::factory()->create([
        'email' => 'linked@example.com',
        'google_id' => 'original-google-id',
    ]);

    $googleUser = new SocialiteUser;
    $googleUser->id = 'different-google-id';
    $googleUser->name = 'Linked User';
    $googleUser->email = 'linked@example.com';
    $googleUser->map(['id' => 'different-google-id', 'name' => 'Linked User', 'email' => 'linked@example.com']);

    $provider = $this->mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.callback'));

    $existingUser->refresh();
    expect($existingUser->google_id)->toBe('original-google-id');
});

test('google callback handles failure gracefully', function () {
    $provider = $this->mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andThrow(new \Exception('Google OAuth error'));
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertGuest();
});

test('google callback auto-verifies email for new users', function () {
    $googleUser = new SocialiteUser;
    $googleUser->id = '555555555';
    $googleUser->name = 'New Google User';
    $googleUser->email = 'newgoogle@example.com';
    $googleUser->map(['id' => '555555555', 'name' => 'New Google User', 'email' => 'newgoogle@example.com']);

    $provider = $this->mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.callback'));

    $user = User::where('email', 'newgoogle@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
});

test('google-only user does not see update password section on profile', function () {
    $user = User::factory()->create([
        'google_id' => 'google-123',
        'password' => null,
    ]);

    $response = $this->actingAs($user)->get('/en/profile');

    $response->assertOk();
    $response->assertSee(__('Your account is connected to Google.'));
    $response->assertDontSee(__('Update Password'));
});

test('dual-method user sees update password section on profile', function () {
    $user = User::factory()->create([
        'google_id' => 'google-456',
    ]);

    $response = $this->actingAs($user)->get('/en/profile');

    $response->assertOk();
    $response->assertSee(__('Your account is connected to Google.'));
    $response->assertSee(__('Update Password'));
});

test('google-only user can delete account by typing DELETE', function () {
    $user = User::factory()->create([
        'google_id' => 'google-789',
        'password' => null,
    ]);

    $response = $this->actingAs($user)->delete('/en/profile', [
        'delete_confirmation' => 'DELETE',
    ]);

    $response->assertRedirect();
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('google-only user cannot delete account with wrong confirmation', function () {
    $user = User::factory()->create([
        'google_id' => 'google-101',
        'password' => null,
    ]);

    $response = $this->actingAs($user)->delete('/en/profile', [
        'delete_confirmation' => 'WRONG',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('email user still deletes account with password', function () {
    $user = User::factory()->create([
        'google_id' => null,
    ]);

    $response = $this->actingAs($user)->delete('/en/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
