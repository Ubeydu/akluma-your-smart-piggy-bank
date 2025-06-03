<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Slug Translations
    |--------------------------------------------------------------------------
    |
    | This file contains the translated URL slugs for all supported languages.
    | The key represents the route identifier, and each language contains
    | the translated slug for that route.
    |
    */

    'routes' => [
        // Main application routes
        'dashboard' => [
            'en' => 'dashboard',
            'tr' => 'panelim',
            'fr' => 'tableau-de-bord',
        ],

        'piggy-banks' => [
            'en' => 'piggy-banks',
            'tr' => 'kumbaralarim',
            'fr' => 'mes-tirelires',
        ],

        'profile' => [
            'en' => 'profile',
            'tr' => 'profilim',
            'fr' => 'profil',
        ],

        'create-piggy-bank' => [
            'en' => 'create-piggy-bank',
            'tr' => 'kumbara-olustur',
            'fr' => 'creer-tirelire',
        ],

        'terms-of-service' => [
            'en' => 'terms-of-service',
            'tr' => 'kullanim-kosullari',
            'fr' => 'conditions-utilisation',
        ],

        'privacy-policy' => [
            'en' => 'privacy-policy',
            'tr' => 'gizlilik-politikasi',
            'fr' => 'politique-confidentialite',
        ],

        // Auth routes
        'login' => [
            'en' => 'login',
            'tr' => 'giris',
            'fr' => 'connexion',
        ],

        'logout' => [
            'en' => 'logout',
            'tr' => 'cikis',
            'fr' => 'deconnexion',
        ],

        'register' => [
            'en' => 'register',
            'tr' => 'kayit-ol',
            'fr' => 'inscription',
        ],

        'forgot-password' => [
            'en' => 'forgot-password',
            'tr' => 'sifremi-unuttum',
            'fr' => 'mot-de-passe-oublie',
        ],

        'reset-password' => [
            'en' => 'reset-password',
            'tr' => 'sifre-sifirla',
            'fr' => 'reinitialiser-mot-de-passe',
        ],

        'verify-email' => [
            'en' => 'verify-email',
            'tr' => 'email-dogrula',
            'fr' => 'verifier-email',
        ],

        'verify-email-with-params' => [
            'en' => 'verify-email/{id}/{hash}',
            'tr' => 'email-dogrula/{id}/{hash}',
            'fr' => 'verifier-email/{id}/{hash}',
        ],

        'email/verification-notification' => [
            'en' => 'email/verification-notification',
            'tr' => 'email/dogrulama-bildirimi',
            'fr' => 'email/notification-verification',
        ],

        'confirm-password' => [
            'en' => 'confirm-password',
            'tr' => 'sifre-onayla',
            'fr' => 'confirmer-mot-de-passe',
        ],

        'password' => [
            'en' => 'password',
            'tr' => 'sifre',
            'fr' => 'mot-de-passe',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Parameter Translations
    |--------------------------------------------------------------------------
    |
    | Translations for route parameters like {piggy_id} → {kumbara_id}
    |
    */

    'parameters' => [
        'piggy_id' => [
            'en' => 'piggy_id',
            'tr' => 'kumbara_id',
            'fr' => 'tirelire_id',
        ],
        'token' => [
            'en' => 'token',
            'tr' => 'token',
            'fr' => 'jeton',
        ],
    ],
];
