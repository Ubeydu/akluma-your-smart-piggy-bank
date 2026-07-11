<?php

use App\Models\User;
use App\Notifications\LocalizedResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/en/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/en/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, LocalizedResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/en/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, LocalizedResetPassword::class, function ($notification) {
        $response = $this->get('/en/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/en/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, LocalizedResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/en/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/en/login');

        return true;
    });
});
