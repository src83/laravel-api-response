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
     * Normalize all API responses to a unified JSON format during the final API response structure check.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip non-standard response types (e.g., file downloads)
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        // If the response is already standardized — return it as is
        if ($response instanceof ApiResponse) {
            return $response;
        }

        // Non-standard response type without a getOriginalContent method — skip it
        if (!method_exists($response, 'getOriginalContent')) {
            return $response;
        }

        /** @var \Illuminate\Http\Response $response */
        $content = $response->getOriginalContent();
        $data = $content;
        $status = $response->getStatusCode();

        // Wrapping the response in a standard, unified structure
        if ($status >= 200 && $status < 300) {
            return ApiResponse::success(data: $data, httpCode: $status);
        }

        return ApiResponse::error(httpCode: $status, details: $data);
    }
}
