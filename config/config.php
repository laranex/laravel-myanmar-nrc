<?php

return [
    /**
     * Only en and mm are allowed
     */
    'locale' => 'en',

    /**
     * Json file to be read.
     * Custom files are welcomed. Example - storage_path("nrc.json")
     **/
    'json_file' => 'nrc.json',

    /**
     * This will determine if the application should use database for validation and parsing of nrc or not
     * true = database driven
     * false = json file driven
     **/
    'db_driven' => true,
];
