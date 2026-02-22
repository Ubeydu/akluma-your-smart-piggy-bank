<?php

use App\Models\User;

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
