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
     * Гарантирует, что все API-ответы возвращаются с корректным JSON-заголовком
     *
     * Laravel автоматически определяет и устанавливает Content-Type: application/json для JsonResponse
     *
     * При необходимости можем через конфиг установить заголовок ответа Content-Type на тот, который
     * запросили в Accept. Но при такой ручной установке Content-Type по Accept браузер ожидает не JSON,
     * но в данных приходит JSON → Chrome выкидывает белый экран. Это всего лишь опция.
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
