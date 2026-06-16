<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Customer Portal Static Password
    |--------------------------------------------------------------------------
    |
    | Shared password used for all customer portal logins until per-customer
    | passwords are implemented.
    |
    */

    'static_password' => env('CUSTOMER_PORTAL_PASSWORD', 'password'),

];
