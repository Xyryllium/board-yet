<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Password Validation Rules
    |--------------------------------------------------------------------------
    |
    | These rules define the password requirements for user registration
    | and password updates throughout the application.
    |
    */

    'password_rules' => [
        'required',
        'string',
        'min:8',
        'regex:/[a-z]/',
        'regex:/[A-Z]/',
        'regex:/[0-9]/',
        'regex:/[@$!%*#?&]/',
    ],

    'password_confirmation_rules' => [
        'required',
        'string',
    ],
];
