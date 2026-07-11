<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Responses;

use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Support\Resolvers\MessageResolver;

final class ApiErrorResponse extends ApiResponse
{
    /**
     * @use ApiErrorResponse::make(404, MessageKeyEnum::NOT_FOUND)
     * @use ApiErrorResponse::make(401, MessageKeyEnum::UNAUTHORIZED)
     * @use ApiErrorResponse::make(401, MessageKeyEnum::UNAUTHORIZED, sysMessage: 'Bad credentials')
     * @use ApiErrorResponse::make(422, MessageKeyEnum::UNPROCESSABLE_CONTENT, details: ['fields' => $errors])
     */
    public static function make(
        int $httpCode,
        string|MessageKeyEnum|null $messageKey = null,
        ?string $guiMessage = null,
        ?string $sysMessage = null,
        mixed $details = null,
    ): ApiResponse {
        if ($messageKey !== null) {
            $resolved = MessageResolver::resolve($messageKey, $guiMessage);
            $messageKey = $resolved->messageKey;
            $guiMessage = $resolved->guiMessage;
        }

        return parent::error(
            httpCode: $httpCode,
            messageKey: $messageKey,
            guiMessage: $guiMessage,
            sysMessage: $sysMessage,
            details: $details,
        );
    }
}
