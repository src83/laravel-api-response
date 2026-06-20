<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetupHeadersApiRequest
{
    /**
     * Гарантирует наличие корректного Accept-заголовка для API-запросов
     *
     * Для запросов без Accept-заголовка — устанавливаем 'Accept: application/json'
     * Если 'Accept: asterisk/asterisk'  —  заменяем на  'Accept: application/json'
     * Разрешены без изменения: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $accept = trim($request->header('Accept', ''));

        if (!$request->headers->has('Accept') || in_array($accept, ['', '*/*'], true)) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
