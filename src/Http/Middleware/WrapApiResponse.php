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
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        if ($response instanceof ApiResponse) {
            return $response;
        }

        if (!method_exists($response, 'getOriginalContent')) {
            return $response;
        }

        $content = $response->getOriginalContent();
        $data    = $content ?? null;
        $status = method_exists($response, 'getStatusCode')
            ? $response->getStatusCode()
            : Response::HTTP_OK;

        if ($status >= 200 && $status < 300) {
            return ApiResponse::success(data: $data, httpCode: $status);
        }

        return ApiResponse::error(httpCode: $status, details: $data);
    }
}
