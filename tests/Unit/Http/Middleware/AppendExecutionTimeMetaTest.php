<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Src83\LaravelApiResponse\Http\Middleware\AppendExecutionTimeMeta;
use Src83\LaravelApiResponse\Tests\TestCase;

final class AppendExecutionTimeMetaTest extends TestCase
{
    private AppendExecutionTimeMeta $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AppendExecutionTimeMeta;
    }

    #[Test]
    public function it_does_nothing_when_disabled(): void
    {
        config(['api_response.show_execution_time' => false]);

        $response = new JsonResponse(['success' => true, 'meta' => null, 'data' => null]);
        /** @var JsonResponse $result */
        $result = $this->middleware->handle(
            Request::create('/api/test'),
            fn () => $response,
        );

        $data = (array) $result->getData(true);
        $this->assertNull($data['meta']);
    }

    #[Test]
    public function it_appends_execution_time_to_successful_response(): void
    {
        config(['api_response.show_execution_time' => true]);

        $response = new JsonResponse(['success' => true, 'meta' => null, 'data' => null]);
        /** @var JsonResponse $result */
        $result = $this->middleware->handle(
            Request::create('/api/test'),
            fn () => $response,
        );

        $data = (array) $result->getData(true);
        $meta = (array) $data['meta'];

        $this->assertArrayHasKey('execution_time', $meta);
        $this->assertIsInt($meta['execution_time']);
        $this->assertGreaterThanOrEqual(0, $meta['execution_time']);
    }

    #[Test]
    public function it_preserves_existing_meta_keys(): void
    {
        config(['api_response.show_execution_time' => true]);

        $response = new JsonResponse([
            'success' => true,
            'meta'    => ['paginator' => ['total' => 10]],
            'data'    => null,
        ]);
        /** @var JsonResponse $result */
        $result = $this->middleware->handle(
            Request::create('/api/test'),
            fn () => $response,
        );

        $data = (array) $result->getData(true);
        $meta = (array) $data['meta'];

        $this->assertArrayHasKey('paginator', $meta);
        $this->assertArrayHasKey('execution_time', $meta);
    }

    #[Test]
    public function it_skips_error_responses(): void
    {
        config(['api_response.show_execution_time' => true]);

        $response = new JsonResponse(['success' => false, 'meta' => null]);
        /** @var JsonResponse $result */
        $result = $this->middleware->handle(
            Request::create('/api/test'),
            fn () => $response,
        );

        $data = (array) $result->getData(true);
        $this->assertNull($data['meta']);
    }

    #[Test]
    public function it_skips_non_json_responses(): void
    {
        config(['api_response.show_execution_time' => true]);

        $response = new Response('plain text');
        /** @var Response $result */
        $result = $this->middleware->handle(
            Request::create('/api/test'),
            fn () => $response,
        );

        $this->assertSame('plain text', $result->getContent());
    }
}
