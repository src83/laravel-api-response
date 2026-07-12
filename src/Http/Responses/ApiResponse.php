<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Src83\LaravelApiResponse\Support\Logging\ApiLoggerInterface;
use Src83\LaravelApiResponse\Support\Logging\DTO\ApiRenderedErrorDTO;
use Src83\LaravelApiResponse\Support\Pagination\ApiPaginator;

/**
 * ApiResponse — an abstract base class containing formatting logic, status code validation, and shared fields.
 * Subclasses — ApiSuccessResponse and ApiErrorResponse — implement the specific factory methods.
 */
class ApiResponse extends JsonResponse
{
    /**
     * Unified JSON success response.
     * Example usage:
     *     return ApiResponse::success($user);
     *     return ApiResponse::success($user, null, 201, 'auth_registration.user_created', 'User created');
     *
     * Example response:
     * {
     *    "success": true,
     *    "http_code": 2XX,
     *    "http_text": "Success code description",
     *    "message": { "key": "...", "gui": "..." } | null,
     *    "meta": { "paginator": {...} } | null,
     *    "data": {...} | null
     * }
     *
     * @param mixed|null $data Response payload (main response data)
     * @param ApiPaginator|null $paginator Pagination metadata
     * @param int $httpCode HTTP status code (default: 200)
     * @param string|null $messageKey Dictionary key used to look up the translation in the specified locale
     * @param string|null $guiMessage Localized message intended for GUI output
     */
    public static function success(
        mixed $data = null,
        ?ApiPaginator $paginator = null,
        int $httpCode = 200,
        ?string $messageKey = null,
        ?string $guiMessage = null,
    ): self {
        $httpText = self::validateHttpCode($httpCode, __METHOD__);

        $response = [
            'success'   => true,
            'http_code' => $httpCode,
            'http_text' => $httpText,
            'message'   => null,
            'meta'      => null,
            'data'      => $data,
        ];

        if ($messageKey !== null || $guiMessage !== null) {
            $response['message'] = [
                'key' => $messageKey,
                'gui' => $guiMessage,
            ];
        }

        if ($paginator !== null) {
            $response['meta']['paginator'] = $paginator->toArray();
        }

        return new self($response, $httpCode);
    }

    /**
     * Unified JSON error response.
     * Example usage:
     *     return ApiResponse::error(404);
     *     return ApiResponse::error(404, 'auth_login.user_not_found', 'User not found');
     *     // see also Handler::render()
     *
     * Example response:
     * {
     *   "success": false,
     *   "http_code": 4XX | 5XX,
     *   "http_text": "Error code description",
     *   "message": { "key": "...", "gui": "...", "sys": "..." } | null,
     *   "details": {...} | null
     * }
     *
     * @param int $httpCode HTTP status code
     * @param string|null $messageKey Dictionary key used to look up the translation in the specified locale
     * @param string|null $guiMessage Localized message intended for GUI output
     * @param string|null $sysMessage Custom message from the exception argument (returns null if not provided)
     * @param mixed|null $details Additional data (e.g., validation errors)
     */
    public static function error(
        int $httpCode,
        ?string $messageKey = null,
        ?string $guiMessage = null,
        ?string $sysMessage = null,
        mixed $details = null,
    ): self {
        $httpText = self::validateHttpCode($httpCode, __METHOD__);

        $response = [
            'success'   => false,
            'http_code' => $httpCode,
            'http_text' => $httpText,
            'message'   => null,
            'details'   => $details,
        ];

        if ($messageKey !== null || $guiMessage !== null || $sysMessage !== null) {
            $response['message'] = [
                'key' => $messageKey,
                'gui' => $guiMessage,
                'sys' => $sysMessage,
            ];
        }

        $responseData = new ApiRenderedErrorDTO(
            httpCode: $httpCode,
            httpText: $httpText,
            messageKey: $messageKey,
            guiMessage: $guiMessage,
            sysMessage: $sysMessage,
            details: $details,
        );
        app(ApiLoggerInterface::class)->captureRenderedError($responseData);

        return new self($response, $httpCode);
    }

    private static function validateHttpCode(int $httpCode, string $method): string
    {
        $method = substr($method, strrpos($method, '\\') + 1).'()';

        // Framework-specific codes not present in Symfony's list
        $extended = [419 => 'Page Expired'];

        if (isset(Response::$statusTexts[$httpCode])) {
            return Response::$statusTexts[$httpCode];
        }

        if (isset($extended[$httpCode])) {
            return $extended[$httpCode];
        }

        // Guard against misconfiguration when an unknown HTTP code is passed in a call
        // Exceptions are caught by the "5XX Default" Handler section
        throw new InvalidArgumentException("Unknown HTTP code: {$httpCode} in {$method}");
    }
}
