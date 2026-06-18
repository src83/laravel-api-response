<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Psr\Container\NotFoundExceptionInterface;
use Src83\LaravelApiResponse\Support\DTO\ApiErrorDTO;
use Src83\LaravelApiResponse\Exceptions\ItemNotFoundException;
use Src83\LaravelApiResponse\Http\Responses\ApiErrorResponse;
use Src83\LaravelApiResponse\Support\Logging\ApiLogger;
use Src83\LaravelApiResponse\Helpers\Data\StringHelper;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Throwable;

/**
 * Add this trait to your App\Exceptions\Handler to enable unified API error responses.
 *
 * Usage:
 *   class Handler extends ExceptionHandler
 *   {
 *       use HandlesApiExceptions;
 *   }
 */
trait HandlesApiExceptions
{
    /**
     * Report or log an exception.
     * @throws Throwable
     */
    public function report(Throwable $e): void
    {
        if (request()?->isApi()) {
            app(ApiLogger::class)->captureThrowableError($e);
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     * @param Request $request
     * @param Throwable $e
     * @return Response|JsonResponse|HttpResponse
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response|JsonResponse|HttpResponse
    {
        if ($e instanceof TokenMismatchException) {
            return parent::render($request, $e);
        }

        // API only: Обрабатываем все исключения в едином JSON-формате
        if ($request->isApi()) {
            $errorData = $this->handleApiException($request, $e);
            return ApiErrorResponse::make(...$errorData->toArray());
        }

        // WEB only: стандартная HTML-страница ошибки
        return parent::render($request, $e);
    }

    /**
     * Единая обработка исключений для API / логика обработки и категоризация ошибок
     */
    protected function handleApiException(Request $request, Throwable $e): ApiErrorDTO
    {
        $isDebug  = config('app.debug') === true;
        $isModule = config('api.is_module_available') === true;

        $module   = $isModule ? $request->apiModule() : null;

        /** Именованные исключения */

        // 400: Bad Request
        if ($e instanceof BadRequestException || $e instanceof BadRequestHttpException) {

            $statusCode = HttpResponse::HTTP_BAD_REQUEST;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: StringHelper::titleToSnakeCase(Response::$statusTexts[$statusCode] ?? 'Bad Request'),
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 401: Authentication (unauthenticated) — Неаутентифицирован
        if ($e instanceof AuthenticationException) {

            $statusCode = HttpResponse::HTTP_UNAUTHORIZED;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: StringHelper::titleToSnakeCase(Response::$statusTexts[$statusCode] ?? 'Unauthorized'),
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 403: Authorization (forbidden) — Неавторизован (доступ запрещён)
        if ($e instanceof AuthorizationException ||
            $e instanceof AccessDeniedException || $e instanceof AccessDeniedHttpException ||
            $e instanceof UnauthorizedException || $e instanceof UnauthorizedHttpException) {

            $statusCode = HttpResponse::HTTP_FORBIDDEN;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: StringHelper::titleToSnakeCase(Response::$statusTexts[$statusCode] ?? 'Forbidden'),
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 404: ItemNotFound — Запись не найдена
        if ($e instanceof ItemNotFoundException) {

            $statusCode = HttpResponse::HTTP_NOT_FOUND;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: 'item_not_found',
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 404: ModelNotFound — Модель не найдена
        if ($e instanceof ModelNotFoundException) {

            $statusCode = HttpResponse::HTTP_NOT_FOUND;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: 'model_not_found',
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 404: NotFound — Не найдено
        if ($e instanceof NotFoundHttpException || $e instanceof NotFoundExceptionInterface) {

            $statusCode = HttpResponse::HTTP_NOT_FOUND;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: 'not_found',
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 405: MethodNotAllowed — Метод не поддерживается
        if ($e instanceof MethodNotAllowedException || $e instanceof MethodNotAllowedHttpException) {

            $statusCode = HttpResponse::HTTP_METHOD_NOT_ALLOWED;
            $details = ($e instanceof MethodNotAllowedException)
                ? ['allowed_methods' => implode(', ', $e->getAllowedMethods())]
                : ['allowed_methods' => $e->getHeaders()['Allow'] ?? null];

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: 'method_not_allowed',
                sysMessage: $e->getMessage() ?: null,
                details: $details,
            );
        }

        // 409: Conflict (Business Conflict)
        if ($e instanceof ConflictHttpException) {

            $statusCode = HttpResponse::HTTP_CONFLICT;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: 'conflict',
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 413: Request Entity Too Large — Размер запроса превышает допустимый
        if ($e instanceof PostTooLargeException) {

            $statusCode = HttpResponse::HTTP_REQUEST_ENTITY_TOO_LARGE;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: 'content_too_large',
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 422: Unprocessable content / Validation error — Ошибка валидации
        if ($e instanceof ValidationException) {

            $statusCode = HttpResponse::HTTP_UNPROCESSABLE_ENTITY;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: 'unprocessable_content',
                sysMessage: $e->getMessage() ?: null,
                details: ['fields' => $e->errors()],
            );
        }

        // 423: Locked — Ресурс заблокирован
        if ($e instanceof LockedHttpException) {

            $statusCode = HttpResponse::HTTP_LOCKED;

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: 'locked',
                sysMessage: $e->getMessage() ?: null,
            );
        }

        /** Неименованные исключения */

        // 4XX: Остальные HttpException - Covers abort() and other such cases
        if ($e instanceof HttpExceptionInterface) {

            $statusCode = $e->getStatusCode();

            return new ApiErrorDTO(
                httpCode: $statusCode,
                messageKey: StringHelper::titleToSnakeCase(Response::$statusTexts[$statusCode] ?? 'HTTP Error'),
                sysMessage: $e->getMessage() ?: null,
            );
        }

        // 5XX: Default — Internal Server Error
        $statusCode = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
        $details    = $isDebug ? [
            'request'   => [
                'time'   => now()->toIso8601String(),
                'method' => $request->method(),
                'uri'    => $request->path(),
                'module' => $module,
                'params' => $request->except(['password', 'token']),
            ],
            'exception' => [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'type'    => get_class($e),
                'line'    => $e->getLine(),
                'code'    => $e->getCode(),
                #'trace' => $e->getTrace()[0],
            ],
        ] : null;

        return new ApiErrorDTO(
            httpCode: $statusCode,
            messageKey: StringHelper::titleToSnakeCase(Response::$statusTexts[$statusCode] ?? 'Internal Server Error'),
            sysMessage: $e->getMessage() ?: null,
            details: $details,
        );
    }
}
