<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Throwable Logging
    |--------------------------------------------------------------------------
    | When true, raw exceptions are logged via the 'api_throwable' channel
    | before being rendered. Filterable per environment via skipped_types.
    */
    'is_throwable_enabled' => env('API_LOG_THROWABLE', true),

    /*
    |--------------------------------------------------------------------------
    | Enable Rendered Error Logging
    |--------------------------------------------------------------------------
    | When true, the final rendered API error response is logged
    | via the 'api_rendered' channel. Filterable per environment via allowed_codes.
    */
    'is_rendered_enabled' => env('API_LOG_RENDERED', true),

    /*
    |--------------------------------------------------------------------------
    | Log Missing Translations
    |--------------------------------------------------------------------------
    | When true, missing translation keys are logged via 'api_missing_translations'.
    | Duplicate misses within a single request are deduplicated automatically.
    */
    'log_missing_translations' => env('API_LOG_MISSING_TRANSLATIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Per-Environment Settings
    |--------------------------------------------------------------------------
    | Configure which exceptions to skip for throwable logging and which
    | HTTP codes to include in rendered logging, per environment.
    */

    'local' => [
        'throwable' => [
            'skipped_types' => [],  // Log everything locally
        ],
        'rendered' => [
            'allowed_codes' => [400, 404, 409, 422, 500],
        ],
    ],

    'testing' => [
        'throwable' => [
            'skipped_types' => [],
        ],
        'rendered' => [
            'allowed_codes' => [],
        ],
    ],

    'staging' => [
        'throwable' => [
            'skipped_types' => [
                AuthenticationException::class,
                AuthorizationException::class,
                UnauthorizedException::class,
                ValidationException::class,
                ModelNotFoundException::class,
                MethodNotAllowedException::class,
                BadRequestException::class,
                TokenMismatchException::class,
            ],
        ],
        'rendered' => [
            'allowed_codes' => [400, 422, 500],
        ],
    ],

    'production' => [
        'throwable' => [
            'skipped_types' => [
                AuthenticationException::class,
                AuthorizationException::class,
                UnauthorizedException::class,
                ValidationException::class,
                ModelNotFoundException::class,
                MethodNotAllowedException::class,
                BadRequestException::class,
                TokenMismatchException::class,
                HttpException::class,
            ],
        ],
        'rendered' => [
            'allowed_codes' => [400, 500],
        ],
    ],

];
