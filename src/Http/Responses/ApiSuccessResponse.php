<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Responses;

use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Support\Pagination\ApiPaginator;
use Src83\LaravelApiResponse\Support\Resolvers\MessageResolver;
use Symfony\Component\HttpFoundation\Response;

final class ApiSuccessResponse extends ApiResponse
{
    /**
     * @use ApiSuccessResponse::make()
     * @use ApiSuccessResponse::make($data)
     * @use ApiSuccessResponse::make($data, null, Response::HTTP_CREATED)
     * @use ApiSuccessResponse::make($data, null, Response::HTTP_CREATED, MessageKeyEnum::CREATED)
     * @use ApiSuccessResponse::make($data, null, Response::HTTP_CREATED, MessageKeyEnum::CREATED, 'Created')
     * @use ApiSuccessResponse::make($data, null, Response::HTTP_OK, null, 'Done')
     */
    public static function make(
        mixed $data = null,
        ?ApiPaginator $paginator = null,
        int $httpCode = Response::HTTP_OK,
        string|MessageKeyEnum|null $messageKey = null,
        ?string $guiMessage = null,
    ): ApiResponse {
        if ($messageKey !== null) {
            $resolved = MessageResolver::resolve($messageKey, $guiMessage);
            $messageKey = $resolved->messageKey;
            $guiMessage = $resolved->guiMessage;
        }

        return parent::success(
            data: $data,
            paginator: $paginator,
            httpCode: $httpCode,
            messageKey: $messageKey,
            guiMessage: $guiMessage,
        );
    }
}
