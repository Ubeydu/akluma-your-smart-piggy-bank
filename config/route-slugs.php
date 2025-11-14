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

        'draft-piggy-banks' => [
            'en' => 'draft-piggy-banks',
            'tr' => 'taslak-kumbaralar',
            'fr' => 'tirelires-brouillons',
        ],

        'vaults' => [
            'en' => 'vaults',
            'tr' => 'kasalar',
            'fr' => 'coffres-forts',
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

        'reset-password/{token}' => [
            'en' => 'reset-password/{token}',
            'tr' => 'sifre-sifirla/{token}',
            'fr' => 'reinitialiser-mot-de-passe/{token}',
        ],

        'store-new-password' => [
            'en' => 'reset-password',
            'tr' => 'yeni-sifre-kaydet',
            'fr' => 'enregistrer-nouveau-mot-de-passe',
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

        // Add these to your route-slugs.php routes array:

        'step-1' => [
            'en' => 'step-1',
            'tr' => 'adim-1',  // From "Step 1 of 3": "Adım 1 - 3"
            'fr' => 'etape-1', // From "Step 1 of 3": "Étape 1 sur 3"
        ],

        'step-2' => [
            'en' => 'step-2',
            'tr' => 'adim-2',  // From "Step 2 of 3": "Adım 2 - 3"
            'fr' => 'etape-2', // From "Step 2 of 3": "Étape 2 sur 3"
        ],

        'step-3' => [
            'en' => 'step-3',
            'tr' => 'adim-3',  // From "Step 3 of 3": "Adım 3 - 3"
            'fr' => 'etape-3', // From "Step 3 of 3": "Étape 3 sur 3"
        ],

        'pick-date' => [
            'en' => 'pick-date',
            'tr' => 'tarih-belirle',  // From "Pick Date": "Tarih Belirle"
            'fr' => 'choisir-date',   // From "Pick Date": "Choisissez la date"
        ],

        'summary' => [
            'en' => 'summary',
            'tr' => 'ozet',     // From "Summary": "Özet"
            'fr' => 'resume',   // From "Summary": "Résumé"
        ],

        'enter-saving-amount' => [
            'en' => 'enter-saving-amount',
            'tr' => 'duzenli-birikim-miktari-belirle',
            'fr' => 'saisir-montant-epargne',
        ],

        'clear' => [
            'en' => 'clear',
            'tr' => 'sil',
            'fr' => 'effacer',
        ],

        'choose-strategy' => [
            'en' => 'choose-strategy',
            'tr' => 'strateji-sec',
            'fr' => 'choisissez-strategie',
        ],

        'show-summary' => [
            'en' => 'show-summary',
            'tr' => 'ozet-goster',
            'fr' => 'afficher-resume',
        ],

        'store' => [
            'en' => 'store',
            'tr' => 'kaydet',
            'fr' => 'enregistrer',
        ],

        'pause' => [
            'en' => 'pause',
            'tr' => 'duraklat',
            'fr' => 'mettre-en-pause',
        ],

        'resume' => [
            'en' => 'resume',
            'tr' => 'devam-et',
            'fr' => 'reprendre',
        ],

        'update-status-cancelled' => [
            'en' => 'update-status-cancelled',
            'tr' => 'durumu-iptal-edildi-olarak-guncelle',
            'fr' => 'mettre-a-jour-statut-annule',
        ],

        'test-date/{piggy_id}' => [
            'en' => 'test-date/{piggy_id}',
            'tr' => 'test-tarihi/{piggy_id}',
            'fr' => 'date-test/{piggy_id}',
        ],

        'test-date/{piggy_id}/clear' => [
            'en' => 'test-date/{piggy_id}/clear',
            'tr' => 'test-tarihi/{piggy_id}/temizle',
            'fr' => 'date-test/{piggy_id}/effacer',
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
        'vault_id' => [
            'en' => 'vault_id',
            'tr' => 'kasa_id',
            'fr' => 'coffre_id',
        ],
    ],
];
