<?php

use App\Models\User;

test('guest is redirected away from admin stats page', function () {
    $this->get('/admin')->assertRedirect();
});

test('guest is redirected away from admin users page', function () {
    $this->get('/admin/users')->assertRedirect();
});

test('regular user cannot access admin stats page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('regular user cannot access admin users page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/users')->assertForbidden();
});

test('admin can access stats page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('admin can access users page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/users')->assertOk();
});

test('suspended user is logged out and redirected to login on any request', function () {
    $user = User::factory()->suspended()->create(['language' => 'en']);

    $response = $this->actingAs($user)->get('/admin');

    $this->assertGuest();
    $response->assertRedirect('/en/login');
});

test('suspended user sees error message on login page after being kicked out', function () {
    $user = User::factory()->suspended()->create(['language' => 'en']);

    $this->actingAs($user)->get('/admin');

    $this->get('/en/login')->assertSeeText('suspended');
});
