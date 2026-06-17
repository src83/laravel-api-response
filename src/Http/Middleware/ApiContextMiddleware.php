<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiContextMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $requestId = $request->header('X-Request-ID') ?? Str::uuid()->toString();

        $request->attributes->set('request_id', $requestId);

        Log::shareContext([
            'env'        => app()->environment(),
            'request_id' => $requestId,
            'user_id'    => $request->user()?->id,
            'ip'         => $request->ip(),
        ]);

        return $next($request);
    }
}
