<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Output Locale
    |--------------------------------------------------------------------------
    |
    | The language a parsed NRC is formatted in when none is passed to the
    | parser. "en" gives "12/DAGAYA(N)123456", "my" gives the Myanmar form.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | JSON Data File
    |--------------------------------------------------------------------------
    |
    | The file that holds the NRC states, townships and types. It feeds the
    | "mm-nrc:seed" command and the JSON backend. Leave it null to use the
    | file bundled with the package, or point it to your own copy, for
    | example storage_path('nrc.json').
    |
    */

    'json_file' => null,

    /*
    |--------------------------------------------------------------------------
    | Database Driven
    |--------------------------------------------------------------------------
    |
    | When true, NRC numbers are validated and parsed against the nrc_states,
    | nrc_townships and nrc_types tables (run the migrations and seed them
    | with "php artisan mm-nrc:seed"). When false, the JSON file is used
    | directly and no database is needed.
    |
    */

    'db_driven' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | The database backend reads the three NRC tables once, keeps them in
    | memory for the rest of the process and stores them in this cache so
    | other requests and workers skip the queries. "store" null uses the
    | default store, "ttl" null keeps the data forever (or set seconds).
    | Set "enabled" to false to keep the data in memory only. Run
    | "php artisan mm-nrc:clear-cache" after changing the tables yourself;
    | "mm-nrc:seed" clears it for you.
    |
    */

    'cache' => [
        'enabled' => true,
        'store' => null,
        'key' => 'laravel-myanmar-nrc',
        'ttl' => null,
    ],

];
