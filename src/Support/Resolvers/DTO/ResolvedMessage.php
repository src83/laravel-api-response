<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Resolvers\DTO;

final readonly class ResolvedMessage
{
    public function __construct(
        public string $messageKey,
        public ?string $guiMessage,
        public ?string $module,
        public ?string $baseKey,
    ) {}
}
