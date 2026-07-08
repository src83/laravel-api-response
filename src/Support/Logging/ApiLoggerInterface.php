<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Logging;

use Src83\LaravelApiResponse\Support\Logging\DTO\ApiRenderedErrorDTO;
use Throwable;

interface ApiLoggerInterface
{
    public function captureThrowableError(Throwable $e): void;

    public function captureRenderedError(ApiRenderedErrorDTO $responseData): void;

    public function translationMissing(array $context): void;
}
