<?php

use App\Models\User;

test('admin:grant grants admin access to an existing user', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->artisan('admin:grant', ['email' => $user->email])
        ->assertExitCode(0);

    expect($user->fresh()->is_admin)->toBeTrue();
});

test('admin:grant is idempotent when user is already an admin', function () {
    $user = User::factory()->admin()->create();

    $this->artisan('admin:grant', ['email' => $user->email])
        ->assertExitCode(0);

    expect($user->fresh()->is_admin)->toBeTrue();
});

test('admin:grant fails with exit code 1 when email does not exist', function () {
    $this->artisan('admin:grant', ['email' => 'nobody@example.com'])
        ->assertExitCode(1);
});

test('admin:grant does not create a new user when email does not exist', function () {
    $this->artisan('admin:grant', ['email' => 'nobody@example.com']);

    $this->assertDatabaseMissing('users', ['email' => 'nobody@example.com']);
});
