<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Responses;

use Illuminate\Pagination\LengthAwarePaginator;
use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Support\Pagination\ApiPaginator;

final class ApiPaginatedCollectionResponse
{
    public static function fromPaginator(
        LengthAwarePaginator $paginator,
        int $httpCode = 200,
        string|MessageKeyEnum|null $messageKey = null,
        ?string $guiMessage = null,
    ): ApiResponse {
        return ApiSuccessResponse::make(
            data: $paginator->items(),
            paginator: ApiPaginator::from($paginator),
            httpCode: $httpCode,
            messageKey: $messageKey,
            guiMessage: $guiMessage,
        );
    }
}
