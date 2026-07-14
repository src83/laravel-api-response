<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SetupHeadersApiResponse
{
    /**
     * Ensures API responses use the correct Content-Type header.
     *
     * Binary file responses are left untouched.
     * Laravel automatically sets 'Content-Type: application/json' for JsonResponse instances.
     *
     * If 'api_response.direct_accept_header' is enabled, Content-Type mirrors the request's Accept header instead.
     * Warning: if Accept is e.g. 'text/html' but the body is still JSON, Chrome may show a blank page — use with caution.
     *
     * If 'api_response.force_json_response' is enabled, Content-Type is forced to 'application/json' regardless of Accept.
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

        return $response;
    }
}
