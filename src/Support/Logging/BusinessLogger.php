<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Logging;

use Illuminate\Support\Facades\Log;

final class BusinessLogger
{
    /** @param array<string, mixed> $context */
    public static function warning(string $event, array $context = []): void
    {
        if (!config('api_response_logging.log_business_warnings')) {
            return;
        }

        Log::channel('api_business_warnings')
            ->warning($event, $context + [
                'env' => app()->environment(),
            ]);
    }
}
