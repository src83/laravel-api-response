<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Logging;

interface TranslationLoggerInterface
{
    public function translationMissing(array $context): void;
}
