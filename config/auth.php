<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web'         => [
            'driver'   => 'session',
            'provider' => 'pam_web',
        ],
        'backend'     => [
            'driver'   => 'session',
            'provider' => 'pam_desktop',
        ],
        'desktop'     => [
            'driver'   => 'session',
            'provider' => 'pam_desktop',
        ],
        'develop'     => [
            'driver'   => 'session',
            'provider' => 'pam_develop',
        ],
        'jwt_backend' => [
            'driver'   => 'jwt',
            'provider' => 'pam_backend',
        ],
        'jwt_web'     => [
            'driver'   => 'jwt',
            'provider' => 'pam_web',
        ],
        'jwt'         => [
            'driver'   => 'jwt',
            'provider' => 'pam',
        ],
        'api'         => [
            'driver'   => 'session',
            'provider' => 'pam_web',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'pam_desktop' => [
            'driver' => 'dailian.pam.desktop',
        ],
        'pam_web'     => [
            'driver' => 'dailian.pam.web',
        ],
        'pam_develop' => [
            'driver' => 'dailian.pam.develop',
        ],
        'pam'         => [
            'driver' => 'dailian.pam',
        ],
    ],
];
