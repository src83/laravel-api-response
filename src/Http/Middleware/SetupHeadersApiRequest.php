<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetupHeadersApiRequest
{
    public function handle(Request $request, Closure $next): mixed
    {
        $accept = trim($request->header('Accept', ''));

        if (!$request->headers->has('Accept') || in_array($accept, ['', '*/*'], true)) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
