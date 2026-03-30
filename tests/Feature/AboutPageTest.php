<?php

// ──────────────────────────────────────────────────
// Page Loads
// ──────────────────────────────────────────────────

it('loads the about page in English', function () {
    app()->setLocale('en');

    $this->get('/en/about')
        ->assertOk()
        ->assertSee('About Akluma');
});

it('loads the about page in Turkish', function () {
    app()->setLocale('tr');

    $this->get('/tr/hakkinda')
        ->assertOk()
        ->assertSee('Akluma Hakkında');
});

it('loads the about page in French', function () {
    app()->setLocale('fr');

    $this->get('/fr/a-propos')
        ->assertOk()
        ->assertSee('À propos d\'Akluma');
});

// ──────────────────────────────────────────────────
// Meta Tags
// ──────────────────────────────────────────────────

it('renders English meta tags', function () {
    app()->setLocale('en');

    $this->get('/en/about')
        ->assertOk()
        ->assertSee('About Akluma - Free Savings Tracker', false)
        ->assertSee('free savings tracker app', false);
});

it('renders Turkish meta tags', function () {
    app()->setLocale('tr');

    $this->get('/tr/hakkinda')
        ->assertOk()
        ->assertSee('Ücretsiz Para Biriktirme Uygulaması', false)
        ->assertSee('para biriktirmenin kolay yolu', false);
});

it('renders French meta tags', function () {
    app()->setLocale('fr');

    $this->get('/fr/a-propos')
        ->assertOk()
        ->assertSee('Application budget gratuite', false)
        ->assertSee('application budget gratuite', false);
});

// ──────────────────────────────────────────────────
// Content Sections
// ──────────────────────────────────────────────────

it('renders all content sections in English', function () {
    app()->setLocale('en');

    $this->get('/en/about')
        ->assertOk()
        ->assertSee('What is Akluma?')
        ->assertSee('Why I Built It')
        ->assertSee('How It Works')
        ->assertSee('Who Is Behind Akluma?')
        ->assertSee('Get in Touch');
});

it('renders the create piggy bank CTA link', function () {
    app()->setLocale('en');

    $this->get('/en/about')
        ->assertOk()
        ->assertSee('Create a Piggy Bank')
        ->assertSee('/en/create-piggy-bank/choose-type', false);
});

// ──────────────────────────────────────────────────
// Sitemap
// ──────────────────────────────────────────────────

it('includes the about page in the sitemap for all locales', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee('/en/about', false)
        ->assertSee('/tr/hakkinda', false)
        ->assertSee('/fr/a-propos', false);
});

// ──────────────────────────────────────────────────
// Footer
// ──────────────────────────────────────────────────

it('shows the footer with about link on the terms page', function () {
    app()->setLocale('en');

    $this->get('/en/terms-of-service')
        ->assertOk()
        ->assertSee('About Akluma')
        ->assertSee('/en/about', false);
});

it('shows the footer with about link for authenticated users', function () {
    app()->setLocale('en');
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user)
        ->get('/en/about')
        ->assertOk()
        ->assertSee('About Akluma');
});
