<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Src83\LaravelApiResponse\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WrapApiResponse
{
    /**
     * Приведим все API-ответы к единому JSON-формату при финальной проверке структуры API-ответа.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Пропускаем нестандартные типы ответов (например, загрузки файлов)
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        // Если ответ уже стандартизирован — сразу возвращаем как есть
        if ($response instanceof ApiResponse) {
            return $response;
        }

        // Нестандартный тип ответа без метода getOriginalContent — пропускаем
        if (!method_exists($response, 'getOriginalContent')) {
            return $response;
        }

        /** @var \Illuminate\Http\Response $response */
        $content = $response->getOriginalContent();
        $data = $content;
        $status = $response->getStatusCode();

        // Оборачиваем ответ в стандартную унифицированную структуру
        if ($status >= 200 && $status < 300) {
            return ApiResponse::success(data: $data, httpCode: $status);
        }

        return ApiResponse::error(httpCode: $status, details: $data);
    }
}
