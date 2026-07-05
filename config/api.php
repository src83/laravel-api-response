<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Direct Accept Header Mode
    |--------------------------------------------------------------------------
    | If true, the response Content-Type will be forced to match the Accept header.
    | Enable only when explicitly needed.
    */
    'direct_accept_header' => env('API_DIRECT_ACCEPT_HEADER', false),

    /*
    |--------------------------------------------------------------------------
    | Force JSON Response
    |--------------------------------------------------------------------------
    | If true, forces Content-Type: application/json on all API responses.
    | Enable only when Laravel's automatic Content-Type detection is incorrect.
    */
    'force_json_response' => env('API_FORCE_JSON_RESPONSE', false),

    /*
    |--------------------------------------------------------------------------
    | Module System
    |--------------------------------------------------------------------------
    | If true, the message key in handleApiException will include the module
    | derived from the request URI. Affects translation lookup.
    */
    'is_module_available' => env('API_IS_MODULE_AVAILABLE', false),

    /*
    |--------------------------------------------------------------------------
    | Translation Lookup Strategy
    |--------------------------------------------------------------------------
    | Determines how translations are resolved in LocalizationResolver:
    |   strict   — looks up only in the current locale's dictionaries.
    |   graceful — tries current locale, then fallback locale (app.fallback_locale).
    | In both cases, module key is tried first (if module system is enabled).
    */
    'translation_lookup' => env('API_TRANSLATION_LOOKUP', 'strict'),

    /*
    |--------------------------------------------------------------------------
    | Module Aliases
    |--------------------------------------------------------------------------
    | When is_module_available is true, the default module is derived from the
    | request URI. Use this map to merge similar modules into one translation key.
    | Key: module derived from request | Value: alias to use instead.
    */
    'module_aliases' => [
        // 'posts_drafts' => 'posts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Execution Time
    |--------------------------------------------------------------------------
    | If true, adds execution_time (ms) to the meta object of successful responses.
    | Useful for diagnosing slow requests without external tools. Disable in production.
    */
    'show_execution_time' => env('API_SHOW_EXECUTION_TIME', false),

];
