<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Feature;

use Illuminate\Http\Request;
use Src83\LaravelApiResponse\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Feature-тест макроса isApi()
 * Проверяются все ключевые сценарии: API-путь, заголовки JSON, Bearer-токен и Sanctum-cookie.
 * php artisan test --filter=RequestIsApiTest
 */
final class RequestIsApiTest extends TestCase
{
    #[Test]
    public function it_detects_api_path(): void
    {
        $request = Request::create('/api/users', 'GET');
        $this->assertTrue($request->isApi());
    }

    #[Test]
    public function it_detects_accept_json_header(): void
    {
        $request = Request::create('/profile', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertTrue($request->isApi());
    }

    #[Test]
    public function it_detects_bearer_token(): void
    {
        $request = Request::create('/user', 'GET', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer some-token',
        ]);

        $this->assertTrue($request->isApi());
    }

    #[Test]
    public function it_detects_sanctum_cookie(): void
    {
        $request = Request::create('/user', 'GET', [], [
            'XSRF-TOKEN' => 'test-xsrf-token',
        ], [], [
            'HTTP_X_XSRF_TOKEN' => 'test-xsrf-token',
        ]);

        $this->assertTrue($request->isApi());
    }

    #[Test]
    public function it_returns_false_for_web_request(): void
    {
        $request = Request::create('/dashboard', 'GET');
        $this->assertFalse($request->isApi());
    }
}
