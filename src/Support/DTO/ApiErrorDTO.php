<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\DTO;

final readonly class ApiErrorDTO
{
    public function __construct(
        public int $httpCode,
        public ?string $messageKey = null,
        public ?string $guiMessage = null,
        public ?string $sysMessage = null,
        public mixed $details = null,
    ) {}

    public function toArray(): array
    {
        return [
            'httpCode'   => $this->httpCode,
            'messageKey' => $this->messageKey,
            'guiMessage' => $this->guiMessage,
            'sysMessage' => $this->sysMessage,
            'details'    => $this->details,
        ];
    }
}
