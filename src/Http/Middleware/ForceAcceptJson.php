<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceAcceptJson
{
    /**
     * Ensures a valid Accept header is present for API requests.
     *
     * Forces 'Accept: application/json' when the header is absent, '' or 'asterisk/asterisk'.
     * Browser Accept headers (text/html, etc.) are passed through unchanged.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $accept = trim($request->header('Accept', ''));

        if (in_array($accept, ['', '*/*'], true)) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
