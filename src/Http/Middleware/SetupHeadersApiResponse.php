<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SetupHeadersApiResponse
{
    /**
     * Гарантирует, что все API-ответы возвращаются с корректным JSON-заголовком
     *
     * Laravel автоматически определяет и устанавливает Content-Type: application/json для JsonResponse
     *
     * При необходимости можем через конфиг установить заголовок ответа Content-Type на тот, который
     * запросили в Accept. Но при такой ручной установке Content-Type по Accept браузер ожидает не JOSN,
     * но в данных приходит JOSN → Chrome выкидывает белый экран. Это всего лишь опция.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
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
