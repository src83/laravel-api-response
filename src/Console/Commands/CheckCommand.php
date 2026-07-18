<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Console\Commands;

use Illuminate\Console\Command;

class CheckCommand extends Command
{
    protected $signature = 'api-response:check
        {--fix : Apply stubs automatically for ACTION REQUIRED files}';

    protected $description = 'Check the Laravel API Response installation status';

    public function handle(): int
    {
        $this->components->info('Laravel API Response — installation check');
        $this->newLine();

        $fix = (bool) $this->option('fix');
        $stubsPath = __DIR__.'/../../../stubs';
        $allDone = true;

        $allDone = $this->checkMarker(
            'app/Http/Kernel.php',
            app_path('Http/Kernel.php'),
            'ForceAcceptJson',
            'Run php artisan api-response:install',
        ) && $allDone;

        $allDone = $this->checkMarker(
            'phpunit.xml',
            base_path('phpunit.xml'),
            'API_IS_MODULE_AVAILABLE',
            'Run php artisan api-response:install',
        ) && $allDone;

        $allDone = $this->checkAndFix(
            'app/Exceptions/Handler.php',
            app_path('Exceptions/Handler.php'),
            'ApiLoggerInterface',
            "$stubsPath/Handler.stub",
            $fix,
        ) && $allDone;

        $allDone = $this->checkAndFix(
            'app/Http/Middleware/Authenticate.php',
            app_path('Http/Middleware/Authenticate.php'),
            'isApi()',
            "$stubsPath/Authenticate.stub",
            $fix,
        ) && $allDone;

        $this->newLine();

        if ($allDone) {
            $this->components->info('All checks passed. Installation is complete.');
        } else {
            $this->components->warn('Some steps require manual action. See above.');
            $this->line('  Run <fg=cyan>php artisan api-response:check --fix</> to apply stubs automatically.');
            $this->newLine();
        }

        return $allDone ? self::SUCCESS : self::FAILURE;
    }

    private function checkMarker(string $label, string $path, string $marker, string $hint): bool
    {
        if (!file_exists($path)) {
            $this->components->twoColumnDetail($label, '<fg=yellow;options=bold>SKIP</> (file not found)');

            return true;
        }

        if (str_contains((string) file_get_contents($path), $marker)) {
            $this->components->twoColumnDetail($label, '<fg=green;options=bold>OK</>');

            return true;
        }

        $this->components->twoColumnDetail($label, '<fg=red;options=bold>ACTION REQUIRED</>');
        $this->line("         <fg=gray>→ $hint</>");

        return false;
    }

    private function checkAndFix(string $label, string $path, string $marker, string $stub, bool $fix): bool
    {
        if (!file_exists($path)) {
            $this->components->twoColumnDetail($label, '<fg=yellow;options=bold>SKIP</> (file not found)');

            return true;
        }

        if (str_contains((string) file_get_contents($path), $marker)) {
            $this->components->twoColumnDetail($label, '<fg=green;options=bold>OK</>');

            return true;
        }

        if ($fix) {
            copy($stub, $path);
            $this->components->twoColumnDetail($label, '<fg=green;options=bold>FIXED</>');

            return true;
        }

        $this->components->twoColumnDetail($label, '<fg=red;options=bold>ACTION REQUIRED</>');
        $this->line('         <fg=gray>→ Merge manually from vendor/src83/laravel-api-response/stubs/'.basename($stub).'</>');

        return false;
    }
}
