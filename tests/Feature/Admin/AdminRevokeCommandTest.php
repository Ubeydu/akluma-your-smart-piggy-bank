<?php

use App\Models\User;

test('admin:revoke revokes admin access from an existing admin', function () {
    $user = User::factory()->admin()->create();

    $this->artisan('admin:revoke', ['email' => $user->email])
        ->assertExitCode(0);

    expect($user->fresh()->is_admin)->toBeFalse();
});

test('admin:revoke is idempotent when user is not already an admin', function () {
    $user = User::factory()->create();

    $this->artisan('admin:revoke', ['email' => $user->email])
        ->assertExitCode(0);

    expect($user->fresh()->is_admin)->toBeFalse();
});

test('admin:revoke fails with exit code 1 when email does not exist', function () {
    $this->artisan('admin:revoke', ['email' => 'nobody@example.com'])
        ->assertExitCode(1);
});

test('admin:revoke does not modify any user when email does not exist', function () {
    $admin = User::factory()->admin()->create();

    $this->artisan('admin:revoke', ['email' => 'nobody@example.com']);

    expect($admin->fresh()->is_admin)->toBeTrue();
});
