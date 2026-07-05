<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'api-response:install';

    protected $description = 'Install the Laravel API Response package';

    public function handle(): int
    {
        $this->components->info('Installing Laravel API Response...');
        $this->newLine();

        $this->publishAssets();
        $this->patchKernel();
        $this->patchLogging();
        $this->patchPhpunit();
        $this->patchEnvFile(base_path('.env'));
        $this->patchEnvFile(base_path('.env.example'));

        $this->newLine();
        $this->components->info('Laravel API Response installed successfully.');

        return self::SUCCESS;
    }

    protected function publishAssets(): void
    {
        $this->callSilently('vendor:publish', ['--tag' => 'api-response-config']);
        $this->callSilently('vendor:publish', ['--tag' => 'api-response-lang']);
        $this->callSilently('vendor:publish', ['--tag' => 'api-response-stubs']);

        $this->components->twoColumnDetail('Publishing config, lang, stubs', '<fg=green;options=bold>DONE</>');
    }

    protected function patchKernel(): void
    {
        $path = app_path('Http/Kernel.php');

        if (!file_exists($path)) {
            $this->components->twoColumnDetail('app/Http/Kernel.php', '<fg=yellow;options=bold>SKIP</> (file not found)');
            return;
        }

        $content = file_get_contents($path);

        if (str_contains($content, 'SetupHeadersApiRequest')) {
            $this->components->twoColumnDetail('app/Http/Kernel.php', '<fg=yellow;options=bold>SKIP</> (already patched)');
            return;
        }

        // Add use imports after HttpKernel import
        $content = str_replace(
            "use Illuminate\Foundation\Http\Kernel as HttpKernel;",
            "use Illuminate\Foundation\Http\Kernel as HttpKernel;\n" .
            "use Src83\LaravelApiResponse\Http\Middleware\ApiContextMiddleware;\n" .
            "use Src83\LaravelApiResponse\Http\Middleware\SetupHeadersApiRequest;\n" .
            "use Src83\LaravelApiResponse\Http\Middleware\SetupHeadersApiResponse;\n" .
            "use Src83\LaravelApiResponse\Http\Middleware\WrapApiResponse;",
            $content
        );

        // Prepend request middleware at the start of the api group
        $apiGroupPos = strpos($content, "'api' => [");
        if ($apiGroupPos !== false) {
            $lineEnd = strpos($content, "\n", $apiGroupPos) + 1;
            $content = substr($content, 0, $lineEnd)
                . "            SetupHeadersApiRequest::class,\n"
                . "            ApiContextMiddleware::class,\n"
                . substr($content, $lineEnd);
        }

        // Append response middleware after SubstituteBindings in the api group
        $apiGroupPos = strpos($content, "'api' => [");
        $substitutePos = strpos($content, 'SubstituteBindings::class,', $apiGroupPos);
        if ($substitutePos !== false) {
            $lineEnd = strpos($content, "\n", $substitutePos) + 1;
            $content = substr($content, 0, $lineEnd)
                . "            WrapApiResponse::class,\n"
                . "            SetupHeadersApiResponse::class,\n"
                . substr($content, $lineEnd);
        }

        file_put_contents($path, $content);
        $this->components->twoColumnDetail('app/Http/Kernel.php', '<fg=green;options=bold>DONE</>');
    }

    protected function patchLogging(): void
    {
        $path = config_path('logging.php');

        if (!file_exists($path)) {
            $this->components->twoColumnDetail('config/logging.php', '<fg=yellow;options=bold>SKIP</> (file not found)');
            return;
        }

        $content = file_get_contents($path);

        if (str_contains($content, 'api_throwable')) {
            $this->components->twoColumnDetail('config/logging.php', '<fg=yellow;options=bold>SKIP</> (already patched)');
            return;
        }

        $channels =
            "\n" .
            "        // Raw exception data (suitable for: Sentry / dev analysis)\n" .
            "        'api_throwable' => [\n" .
            "            'driver' => 'single',\n" .
            "            'path' => storage_path('logs/api_throwable.log'),\n" .
            "            'level' => env('LOG_LEVEL', 'error'),\n" .
            "        ],\n" .
            "\n" .
            "        // Rendered API error responses (suitable for: Elastic / ClickHouse)\n" .
            "        'api_rendered' => [\n" .
            "            'driver' => 'single',\n" .
            "            'path' => storage_path('logs/api_rendered.log'),\n" .
            "            'level' => env('LOG_LEVEL', 'error'),\n" .
            "        ],\n" .
            "\n" .
            "        // Missing translation keys\n" .
            "        'api_missing_translations' => [\n" .
            "            'driver' => 'single',\n" .
            "            'path' => storage_path('logs/api_missing_translations.log'),\n" .
            "            'level' => 'warning',\n" .
            "        ],\n" .
            "\n" .
            "        // Business anomalies and violated business expectations\n" .
            "        'api_business' => [\n" .
            "            'driver' => 'single',\n" .
            "            'path' => storage_path('logs/api_business.log'),\n" .
            "            'level' => 'warning',\n" .
            "        ],\n";

        $content = str_replace("'channels' => [", "'channels' => [" . $channels, $content);

        file_put_contents($path, $content);
        $this->components->twoColumnDetail('config/logging.php', '<fg=green;options=bold>DONE</>');
    }

    protected function patchPhpunit(): void
    {
        $path = base_path('phpunit.xml');

        if (!file_exists($path)) {
            $this->components->twoColumnDetail('phpunit.xml', '<fg=yellow;options=bold>SKIP</> (file not found)');
            return;
        }

        $content = file_get_contents($path);

        if (str_contains($content, 'API_IS_MODULE_AVAILABLE')) {
            $this->components->twoColumnDetail('phpunit.xml', '<fg=yellow;options=bold>SKIP</> (already patched)');
            return;
        }

        $envVars =
            "        <env name=\"API_IS_MODULE_AVAILABLE\" value=\"true\"/>\n" .
            "        <env name=\"API_LOG_THROWABLE\" value=\"false\"/>\n" .
            "        <env name=\"API_LOG_RENDERED\" value=\"false\"/>\n";

        $content = str_replace('    </php>', $envVars . '    </php>', $content);

        file_put_contents($path, $content);
        $this->components->twoColumnDetail('phpunit.xml', '<fg=green;options=bold>DONE</>');
    }

    protected function patchEnvFile(string $path): void
    {
        $filename = basename($path);

        if (!file_exists($path)) {
            $this->components->twoColumnDetail($filename, '<fg=yellow;options=bold>SKIP</> (file not found)');
            return;
        }

        $content = file_get_contents($path);

        if (str_contains($content, 'API_IS_MODULE_AVAILABLE')) {
            $this->components->twoColumnDetail($filename, '<fg=yellow;options=bold>SKIP</> (already patched)');
            return;
        }

        $block =
            "\n# Laravel-Api-Response\n" .
            "API_DIRECT_ACCEPT_HEADER=false\n" .
            "API_FORCE_JSON_RESPONSE=false\n" .
            "API_IS_MODULE_AVAILABLE=false\n" .
            "API_TRANSLATION_LOOKUP=strict\n" .
            "\n" .
            "API_LOG_THROWABLE=true\n" .
            "API_LOG_RENDERED=true\n" .
            "API_LOG_MISSING_TRANSLATIONS=true\n" .
            "API_LOG_BUSINESS_WARNINGS=true\n" .
            "# /Laravel-Api-Response\n";

        file_put_contents($path, $content . $block);
        $this->components->twoColumnDetail($filename, '<fg=green;options=bold>DONE</>');
    }
}
