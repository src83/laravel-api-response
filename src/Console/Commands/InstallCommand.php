<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'api-response:install 
        {--force : Overwrite Handler.php and Authenticate.php even if they already exist}';

    protected $description = 'Install the Laravel API Response package';

    public function handle(): int
    {
        $this->components->info('Installing Laravel API Response...');
        $this->newLine();

        $this->publishAssets();
        $this->patchKernel();
        $this->patchPhpunit();
        $this->patchEnvFile(base_path('.env'));
        $this->patchEnvFile(base_path('.env.example'));

        $this->newLine();
        $this->components->info('Laravel API Response installed successfully.');

        return self::SUCCESS;
    }

    protected function publishAssets(): void
    {
        $this->publishGroup('api-response-config', [
            'config/api_response.php'         => config_path('api_response.php'),
            'config/api_response_logging.php' => config_path('api_response_logging.php'),
        ]);

        $this->publishGroup('api-response-lang', [
            'lang/en/api_response.php' => lang_path('en/api_response.php'),
            'lang/ru/api_response.php' => lang_path('ru/api_response.php'),
        ]);

        $this->publishStubs();
    }

    protected function publishStubs(): void
    {
        $isFresh   = (bool) $this->option('force');
        $stubsPath = __DIR__ . '/../../../stubs';

        $testDest = base_path('tests/Feature/Api/ExceptionHandlerTest.php');
        if (!file_exists($testDest)) {
            $testDir = dirname($testDest);
            if (!is_dir($testDir) && !mkdir($testDir, 0755, true) && !is_dir($testDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" could not be created', $testDir));
            }
            copy("$stubsPath/ExceptionHandlerTest.stub", $testDest);
            $this->components->twoColumnDetail('tests/Feature/Api/ExceptionHandlerTest.php', '<fg=green;options=bold>DONE</>');
        } else {
            $this->components->twoColumnDetail('tests/Feature/Api/ExceptionHandlerTest.php', '<fg=yellow;options=bold>SKIP</> (already exists)');
        }

        $this->publishStubFile(
            source:  "$stubsPath/Handler.stub",
            dest:    app_path('Exceptions/Handler.php'),
            label:   'app/Exceptions/Handler.php',
            marker:  'ApiLoggerInterface',
            isFresh: $isFresh,
        );

        $this->publishStubFile(
            source:  "$stubsPath/Authenticate.stub",
            dest:    app_path('Http/Middleware/Authenticate.php'),
            label:   'app/Http/Middleware/Authenticate.php',
            marker:  'isApi()',
            isFresh: $isFresh,
        );
    }

    protected function publishStubFile(string $source, string $dest, string $label, string $marker, bool $isFresh): void
    {
        if ($isFresh) {
            copy($source, $dest);
            $this->components->twoColumnDetail($label, '<fg=green;options=bold>DONE</>');
            return;
        }

        if (!file_exists($dest)) {
            copy($source, $dest);
            $this->components->twoColumnDetail($label, '<fg=green;options=bold>DONE</>');
            return;
        }

        if (str_contains(file_get_contents($dest), $marker)) {
            $this->components->twoColumnDetail($label, '<fg=yellow;options=bold>SKIP</> (already configured)');
            return;
        }

        $this->components->twoColumnDetail($label, '<fg=red;options=bold>ACTION REQUIRED</> — merge manually from stubs/' . basename($source));
    }

    protected function publishGroup(string $tag, array $files): void
    {
        $existedBefore = [];
        foreach ($files as $path) {
            $existedBefore[$path] = file_exists($path);
        }

        $this->callSilently('vendor:publish', ['--tag' => $tag]);

        foreach ($files as $label => $path) {
            if ($existedBefore[$path]) {
                $this->components->twoColumnDetail($label, '<fg=yellow;options=bold>SKIP</> (already exists)');
            } else {
                $this->components->twoColumnDetail($label, '<fg=green;options=bold>DONE</>');
            }
        }
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
            "API_SHOW_EXECUTION_TIME=false\n" .
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
