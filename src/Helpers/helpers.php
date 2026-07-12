<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Src83\LaravelApiResponse\Exceptions\DomainLayerException;

if (!function_exists('app_terminate')) {
    /**
     * Throw an DomainLayerException with the given error code and message.
     *
     * This function is used when there is no field directly associated with
     * the error. In any other case the standard Laravel Validator must be
     * used instead.
     *
     * @throws DomainLayerException
     */
    function app_terminate(string $code, string $message): never
    {
        throw DomainLayerException::withMessages([$code => [$message]]);
    }
}

if (!function_exists('debug_log')) {
    /**
     * Simplified debug-only logger — it works when APP_DEBUG is true
     */
    function debug_log(mixed $data, ?string $context = null): void
    {
        if (config('app.debug')) {
            Log::debug($context ? strtoupper($context).'::' : 'DEBUG: ', (array) $data);
        }
    }
}
