<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/en/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/en/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
        'privacy' => true,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/en/dashboard');
});
