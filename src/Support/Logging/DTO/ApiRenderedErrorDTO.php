<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Support\Logging\DTO;

final readonly class ApiRenderedErrorDTO
{
    public function __construct(
        public int     $httpCode,
        public string  $httpText,
        public ?string $messageKey = null,
        public ?string $guiMessage = null,
        public ?string $sysMessage = null,
        public mixed   $details = null,
    ) {}

    public function toArray(): array
    {
        return [
            'httpCode'   => $this->httpCode,
            'httpText'   => $this->httpText,
            'messageKey' => $this->messageKey,
            'guiMessage' => $this->guiMessage,
            'sysMessage' => $this->sysMessage,
            'details'    => $this->details,
        ];
    }
}
