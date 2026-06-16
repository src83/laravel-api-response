<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Src83\LaravelApiResponse\Support\Logging\ApiLogger;
use Src83\LaravelApiResponse\Support\Logging\TranslationLoggerInterface;

class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/api.php', 'api');
        $this->mergeConfigFrom(__DIR__ . '/../config/api_logging.php', 'api_logging');

        $this->app->bind(TranslationLoggerInterface::class, ApiLogger::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/api.php'         => config_path('api.php'),
                __DIR__ . '/../config/api_logging.php' => config_path('api_logging.php'),
            ], 'api-response-config');

            $this->publishes([
                __DIR__ . '/../lang' => lang_path('vendor/api-response'),
            ], 'api-response-lang');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'api-response');

        $this->registerRequestMacros();
    }

    protected function registerRequestMacros(): void
    {
        Request::macro('isApi', function (): bool {
            /** @var \Illuminate\Http\Request $this */
            $path      = $this->path();
            $isApiPath = str_starts_with($path, 'api') && (strlen($path) === 3 || $path[3] === '/');

            return $isApiPath
                || $this->expectsJson()
                || $this->bearerToken() !== null
                || ($this->hasCookie('XSRF-TOKEN') && $this->hasHeader('X-XSRF-TOKEN'));
        });

        Request::macro('apiModule', function (): ?string {
            /** @var \Illuminate\Http\Request $this */
            if (!$this->isApi()) {
                return null;
            }

            $module = $this->path();
            $module = preg_replace(['/^(\/)?api(\/)?/', '/\d/'], '', $module);
            $module = preg_replace('/[\/_-]+/', '_', $module);
            $module = trim($module, '_');

            return $module ?: null;
        });
    }
}
