<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Cross-Origin Resource Sharing (CORS) Configuration
     |--------------------------------------------------------------------------
     |
     | clientux CORS Strategy:
     |
     | - Widget public routes (widget.js, widget-config, widget-submit):
     |   Open to ALL origins — the widget must be embeddable on any customer site.
     |
     | - All other app routes (dashboard, leads, clients, API, etc.):
     |   Restricted to the same origin (APP_URL) only.
     |
     | The widget routes bypass CSRF via VerifyCsrfToken in web.php.
     | This file handles browser CORS pre-flight (OPTIONS) requests.
     |
     */

    'paths' => [
        // Widget public endpoints — open CORS
        'widget.js',
        'widget-config',
        'widget-submit',

        // Sanctum (if used later for API tokens)
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

    /*
     | '*' here means any origin can embed the widget.
     | This is intentional for the embeddable widget use case.
     | All other routes are NOT listed in 'paths' and therefore
     | are NOT subject to permissive CORS headers.
     */
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Accept',
        'X-Requested-With',
    ],

    'exposed_headers' => [],

    // Cache pre-flight for 2 hours to reduce OPTIONS requests
    'max_age' => 7200,

    // Must be false when allowed_origins is '*'
    'supports_credentials' => false,

];