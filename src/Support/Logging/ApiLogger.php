<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Logging;

use Illuminate\Support\Facades\Log;
use Src83\LaravelApiResponse\Support\Logging\DTO\ApiRenderedErrorDTO;
use Throwable;

final class ApiLogger implements ApiLoggerInterface
{
    /** @var array<string, mixed> */
    private array $request;

    /** @var array<string, bool> */
    private static array $loggedTranslationMisses = [];

    public function __construct()
    {
        if (app()->runningInConsole()) {
            $this->request = [];

            return;
        }

        $this->request = [
            'method' => request()->method(),
            'url'    => request()->fullUrl(),
            'params' => request()->except(['password', 'token']),
        ];
    }

    public function captureThrowableError(Throwable $e): void
    {
        if (!$this->isThrowableEnabled()) {
            return;
        }

        if (!$this->shouldLogThrowable($e)) {
            return;
        }

        $this->storeThrowableEvent($e);
    }

    // -----------------------------

    public function captureRenderedError(ApiRenderedErrorDTO $responseData): void
    {
        if (!$this->isRenderedEnabled()) {
            return;
        }

        if (!$this->shouldLogRendered($responseData)) {
            return;
        }

        $this->storeRenderedEvent($responseData);
    }

    // ==========================================================================

    private function isThrowableEnabled(): bool
    {
        return config('api_response_logging.log_throwable') === true;
    }

    private function isRenderedEnabled(): bool
    {
        return config('api_response_logging.log_rendered') === true;
    }

    // ==========================================================================

    private function shouldLogThrowable(Throwable $e): bool
    {
        $currentEnv = app()->environment();
        $skippedTypes = (array) config("api_response_logging.$currentEnv.throwable.skipped_types", []);

        foreach ($skippedTypes as $type) {
            if ($e instanceof $type) {
                return false;
            }
        }

        return true;
    }

    private function shouldLogRendered(ApiRenderedErrorDTO $responseData): bool
    {
        $currentEnv = app()->environment();
        $allowedCodes = (array) config("api_response_logging.$currentEnv.rendered.allowed_codes", []);

        return in_array($responseData->httpCode, $allowedCodes, true);
    }

    private function storeThrowableEvent(Throwable $e): void
    {
        Log::channel('api_throwable')
            ->error('API Throwable', [
                'stage'     => 'api_throwable',
                'request'   => $this->request,
                'exception' => [
                    'message' => $e->getMessage(),
                    'type'    => get_class($e),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ],
            ]);
    }

    private function storeRenderedEvent(ApiRenderedErrorDTO $responseData): void
    {
        Log::channel('api_rendered')
            ->error('API Rendered', [
                'stage'    => 'api_rendered',
                'request'  => $this->request,
                'response' => $responseData->toArray(),
            ]);
    }

    // ==========================================================================

    /** @param array<string, mixed> $context */
    public function translationMissing(array $context): void
    {
        if (!config('api_response_logging.log_missing_translations')) {
            return;
        }

        // --- защита от дублей ---
        $fingerprint = implode('|', [
            $context['locale'] ?? '',
            $context['module'] ?? '',
            $context['key'] ?? '',
            $context['level'] ?? '',
            $context['strategy'] ?? '',
        ]);

        if (isset(self::$loggedTranslationMisses[$fingerprint])) {
            return;
        }

        self::$loggedTranslationMisses[$fingerprint] = true;
        // --- конец защиты ---

        Log::channel('api_missing_translations')
            ->warning('API translation missing', [
                'locale'   => $context['locale'] ?? null,
                'module'   => $context['module'] ?? null,
                'key'      => $context['key'] ?? null,
                'level'    => $context['level'] ?? null,
                'strategy' => $context['strategy'] ?? null,
                'env'      => app()->environment(),
            ]);
    }
}
