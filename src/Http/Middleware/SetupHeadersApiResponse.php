<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SetupHeadersApiResponse
{
    /**
     * Ensures API responses use the correct Content-Type and can enrich JSON responses with execution time metadata.
     *
     * Binary file responses are left untouched.
     * Laravel automatically sets 'Content-Type: application/json' for JsonResponse instances.
     *
     * If 'api_response.direct_accept_header' is enabled, Content-Type mirrors the request's Accept header instead.
     * Warning: if Accept is e.g. 'text/html' but the body is still JSON, Chrome may show a blank page — use with caution.
     *
     * If 'api_response.force_json_response' is enabled, Content-Type is forced to 'application/json' regardless of Accept.
     *
     * If 'api_response.show_execution_time' is enabled, successful JSON responses get an additional
     * 'meta.execution_time' field (in milliseconds).
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof BinaryFileResponse) {
            return $response;
        }

        if (config('api_response.direct_accept_header')) {
            $accept = $request->header('Accept');
            $response->headers->set('Content-Type', $accept);
        }

        if (config('api_response.force_json_response')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        if ($response instanceof JsonResponse && config('api_response.show_execution_time')) {
            $data = (array) $response->getData(true);
            if (($data['success'] ?? false) === true) {
                $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
                $data['meta'] = array_merge(
                    is_array($data['meta']) ? $data['meta'] : [],
                    ['execution_time' => (int) round((microtime(true) - $startTime) * 1000)],
                );
                $response->setData($data);
            }
        }

        return $response;
    }
}
