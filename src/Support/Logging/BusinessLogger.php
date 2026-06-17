<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Logging;

use Illuminate\Support\Facades\Log;

final class BusinessLogger
{
    public static function warning(string $event, array $context = []): void
    {
        if (!config('api_logging.log_business_warnings')) {
            return;
        }

        Log::channel('api_business')
            ->warning($event, $context + [
                'env' => app()->environment(),
            ]);
    }
}
