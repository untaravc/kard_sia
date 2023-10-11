<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],

        'lecture' => [
            'driver' => 'session',
            'provider' => 'lectures',
        ],

        'student' => [
            'driver' => 'session',
            'provider' => 'students',
        ]
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],

        'lectures' => [
            'driver' => 'eloquent',
            'model' => App\Models\Lecture::class,
        ],

        'students' => [
            'driver' => 'eloquent',
            'model' => App\Models\Student::class,
        ],

    ],

    'passwords' => [
        'users' => [
            'provider'  => 'users',
            'table'     => 'password_resets',
            'expire'    => 60,
            'throttle'  => 60,
        ],
        'lectures' => [
            'provider'  => 'lectures',
            'table'     => 'password_reset',
            'expire'    => 60,
        ],
        'students' => [
            'provider'  => 'students',
            'table'     => 'password_reset',
            'expire'    => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

];
