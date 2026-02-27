<?php

use App\Models\PiggyBank;
use App\Models\User;
use App\Models\Vault;

test('admin sees all users on the users page', function () {
    $admin = User::factory()->admin()->create();
    $others = User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    foreach ($others as $user) {
        $response->assertSeeText($user->name);
    }
});

test('admin can search users by name', function () {
    $admin = User::factory()->admin()->create();
    $match = User::factory()->create(['name' => 'Findable User']);
    $noMatch = User::factory()->create(['name' => 'Someone Else']);

    $response = $this->actingAs($admin)->get('/admin/users?search=Findable');

    $response->assertOk();
    $response->assertSeeText('Findable User');
    $response->assertDontSeeText('Someone Else');
});

test('admin can search users by email', function () {
    $admin = User::factory()->admin()->create();
    $match = User::factory()->create(['email' => 'needle@example.com']);
    $noMatch = User::factory()->create(['email' => 'other@example.com']);

    $response = $this->actingAs($admin)->get('/admin/users?search=needle');

    $response->assertOk();
    $response->assertSeeText('needle@example.com');
    $response->assertDontSeeText('other@example.com');
});

test('admin can view a user detail page', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->get("/admin/users/{$user->id}")->assertOk();
});

test('admin can suspend a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/suspend");

    expect($user->fresh()->suspended_at)->not->toBeNull();
});

test('admin can unsuspend a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->suspended()->create();

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/unsuspend");

    expect($user->fresh()->suspended_at)->toBeNull();
});

test('admin can delete a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->delete("/admin/users/{$user->id}");

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin cannot suspend themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch("/admin/users/{$admin->id}/suspend");

    expect($admin->fresh()->suspended_at)->toBeNull();
});

test('admin cannot delete themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('regular user cannot suspend another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)->patch("/admin/users/{$target->id}/suspend")->assertForbidden();
});

test('regular user cannot delete another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)->delete("/admin/users/{$target->id}")->assertForbidden();
});

test('user list shows piggy bank and vault counts', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $vault = Vault::factory()->create(['user_id' => $user->id]);

    PiggyBank::factory()->active()->create(['user_id' => $user->id]);
    PiggyBank::factory()->active()->create(['user_id' => $user->id, 'vault_id' => $vault->id]);
    PiggyBank::factory()->paused()->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    $response->assertSeeText('3 total');
    $response->assertSeeText('2 active');
    $response->assertSeeText('1 active in 1 vault');
});

test('user detail shows piggy bank and vault counts', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $vault = Vault::factory()->create(['user_id' => $user->id]);

    PiggyBank::factory()->active()->create(['user_id' => $user->id]);
    PiggyBank::factory()->active()->create(['user_id' => $user->id, 'vault_id' => $vault->id]);
    PiggyBank::factory()->paused()->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->get("/admin/users/{$user->id}");

    $response->assertOk();
    $response->assertSeeText('3 total');
    $response->assertSeeText('2 active');
    $response->assertSeeText('1 active connected');
});

test('user list shows email verification for non-google users', function () {
    $admin = User::factory()->admin()->create();
    $verified = User::factory()->create([
        'google_id' => null,
        'email_verified_at' => now()->subDays(30),
    ]);
    $unverified = User::factory()->create([
        'google_id' => null,
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    $response->assertSeeText('Verified:');
    $response->assertSeeText('Not verified');
});

test('user list does not show email verification for google users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create([
        'google_id' => '123456789',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    $response->assertDontSeeText('Verified:');
    $response->assertDontSeeText('Not verified');
});
