<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('piggy-banks.index', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

it('sets remember me cookie when remember checkbox is checked', function () {
    $user = User::factory()->create();

    $response = $this->post('/en/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => '1',
    ]);

    $this->assertAuthenticated();
    $response->assertCookie(Auth::guard()->getRecallerName());
});

it('does not set remember me cookie when remember checkbox is unchecked', function () {
    $user = User::factory()->create();

    $response = $this->post('/en/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertCookieMissing(Auth::guard()->getRecallerName());
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
