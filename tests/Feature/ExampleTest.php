<?php

it('redirects the base URL to the localized welcome page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/en');
});

it('returns a successful response for the localized welcome page', function () {
    $response = $this->get('/en');

    $response->assertStatus(200);
});
