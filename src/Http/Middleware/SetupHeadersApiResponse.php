<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SetupHeadersApiResponse
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof BinaryFileResponse) {
            return $response;
        }

        if (config('api.direct_accept_header')) {
            $accept = $request->header('Accept');
            $response->headers->set('Content-Type', $accept);
        }

        if (config('api.force_json_response')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
