<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Src83\LaravelApiResponse\Console\Commands\InstallCommand;
use Src83\LaravelApiResponse\Support\Logging\ApiLogger;
use Src83\LaravelApiResponse\Support\Logging\ApiLoggerInterface;

class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/api_response.php', 'api_response');
        $this->mergeConfigFrom(__DIR__.'/../config/api_response_logging.php', 'api_response_logging');

        foreach ((array) config('api_response_logging.channels', []) as $name => $channel) {
            config(["logging.channels.$name" => $channel]);
        }

        $this->app->bind(ApiLoggerInterface::class, ApiLogger::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->registerRequestMacros();
    }

    protected function bootForConsole(): void
    {
        $this->commands([InstallCommand::class]);

        $this->publishes([
            __DIR__.'/../config/api_response.php'         => config_path('api_response.php'),
            __DIR__.'/../config/api_response_logging.php' => config_path('api_response_logging.php'),
        ], 'api-response-config');

        $this->publishes([
            __DIR__.'/../lang/en' => lang_path('en'),
            __DIR__.'/../lang/ru' => lang_path('ru'),
        ], 'api-response-lang');

        $this->publishes([
            __DIR__.'/../stubs/Handler.stub'              => app_path('Exceptions/Handler.php'),
            __DIR__.'/../stubs/Authenticate.stub'         => app_path('Http/Middleware/Authenticate.php'),
            __DIR__.'/../stubs/ExceptionHandlerTest.stub' => base_path('tests/Feature/Api/ExceptionHandlerTest.php'),
        ], 'api-response-stubs');
    }

    protected function registerRequestMacros(): void
    {
        Request::macro('isApi', function (): bool {
            /** @var \Illuminate\Http\Request $this */
            $path = $this->path();
            $isApiPath = str_starts_with($path, 'api') && (strlen($path) === 3 || $path[3] === '/');

            $expectsJson = $this->expectsJson();
            $hasBearerToken = $this->bearerToken() !== null;
            $hasSanctumCookie = $this->hasCookie('XSRF-TOKEN') && $this->hasHeader('X-XSRF-TOKEN');

            return $isApiPath || $expectsJson || $hasBearerToken || $hasSanctumCookie;
        });

        Request::macro('apiModule', function (): ?string {
            /** @var \Illuminate\Http\Request $this */
            if (!$this->isApi()) {
                return null;
            }

            $module = $this->path();
            $module = preg_replace(['/^(\/)?api(\/)?/', '/\d/'], '', $module) ?? '';
            $module = preg_replace('/[\/_-]+/', '_', $module) ?? '';
            $module = trim($module, '_');

            return $module ?: null;
        });
    }
}
