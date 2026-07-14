<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppendExecutionTimeMeta
{
    /**
     * Enriches successful JSON API responses with execution time metadata.
     *
     * When 'api_response.show_execution_time' is enabled, injects 'meta.execution_time'
     * (in milliseconds) into responses where 'success' is true.
     * Non-JSON responses and error responses are left untouched.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (!$response instanceof JsonResponse || !config('api_response.show_execution_time')) {
            return $response;
        }

        $data = (array) $response->getData(true);

        if (($data['success'] ?? false) !== true) {
            return $response;
        }

        $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        $data['meta'] = array_merge(
            is_array($data['meta']) ? $data['meta'] : [],
            ['execution_time' => (int) round((microtime(true) - $startTime) * 1000)],
        );
        $response->setData($data);

        return $response;
    }
}
