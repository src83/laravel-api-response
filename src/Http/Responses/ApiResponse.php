<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Src83\LaravelApiResponse\Support\Logging\ApiLogger;
use Src83\LaravelApiResponse\Support\Logging\DTO\ApiRenderedErrorDTO;
use Src83\LaravelApiResponse\Support\Pagination\ApiPaginator;

/**
 * ApiResponse — абстрактная база, содержит логику форматирования, валидации кода, общие поля.
 * Наследники — ApiSuccessResponse и ApiErrorResponse реализуют конкретные фабричные методы.
 */
class ApiResponse extends JsonResponse
{
    /**
     * Унифицированный JSON-ответ об успехе.
     * Пример вызова:
     *     return ApiResponse::success($user);
     *     return ApiResponse::success($user, null, 201, 'auth_registration.user_created', 'Пользователь создан');
     *
     * Пример ответа:
     * {
     *    "success": true,
     *    "http_code": 2XX,
     *    "http_text": "Success code description",
     *    "message": { "key": "...", "gui": "..." } | null,
     *    "meta": { "paginator": {...} } | null,
     *    "data": {...} | null
     * }
     *
     * @param mixed|null $data Основные данные ответа
     * @param ApiPaginator|null $paginator Метаданные пагинации
     * @param int $httpCode HTTP-код (по умолчанию 200)
     * @param string|null $messageKey Словарный ключ для поиска перевода в указанной локали
     * @param string|null $guiMessage Локализованное сообщение для вывода в GUI
     * @return self
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
     * Унифицированный JSON-ответ об ошибке.
     * Пример вызова:
     *     return ApiResponse::error(404);
     *     return ApiResponse::error(404, 'auth_login.user_not_found', 'Пользователь не найден');
     *     см. также в Handler@render
     *
     * Пример ответа:
     * {
     *   "success": false,
     *   "http_code": 4XX | 5XX,
     *   "http_text": "Error code description",
     *   "message": { "key": "...", "gui": "...", "sys": "..." } | null,
     *   "details": {...} | null
     * }
     *
     * @param int $httpCode HTTP-код
     * @param string|null $messageKey Словарный ключ для поиска перевода в указанной локали
     * @param string|null $guiMessage Локализованное сообщение для вывода в GUI
     * @param string|null $sysMessage Кастомное сообщение из аргумента исключения (если не указано - возвращает null)
     * @param mixed|null $details Дополнительные данные (например, ошибки валидации)
     * @return self
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
            httpCode:   $httpCode,
            httpText:   $httpText,
            messageKey: $messageKey,
            guiMessage: $guiMessage,
            sysMessage: $sysMessage,
            details:    $details,
        );
        app(ApiLogger::class)->captureRenderedError($responseData);

        return new self($response, $httpCode);
    }

    private static function validateHttpCode(int $httpCode, string $method): string
    {
        $method = substr($method, strrpos($method, '\\') + 1) . '()';

        // Исключения перехватываются Handler-секцией "Default (5XX)"
        if (!isset(Response::$statusTexts[$httpCode])) {
            throw new InvalidArgumentException("Unknown HTTP code: {$httpCode} in {$method}");
        }

        return Response::$statusTexts[$httpCode];
    }
}
