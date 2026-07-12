<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Support\Resolvers;

use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Src83\LaravelApiResponse\Support\Resolvers\ModuleResolver;
use Src83\LaravelApiResponse\Tests\TestCase;

final class ModuleResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        Request::flushMacros();
        Mockery::close();
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('request', Request::create('/test', 'GET'));
    }

    #[Test]
    public function it_returns_prefix_even_if_modules_are_disabled(): void
    {
        config(['api_response.is_module_available' => false]);

        $result = ModuleResolver::resolve('users');

        $this->assertSame('users', $result);
    }

    #[Test]
    public function it_returns_prefix_even_if_modules_are_enabled(): void
    {
        config(['api_response.is_module_available' => true]);

        $this->mockRequestModule('admin');

        $result = ModuleResolver::resolve('users');

        $this->assertSame('users', $result);
    }

    #[Test]
    public function it_returns_null_when_modules_are_disabled_and_prefix_is_null(): void
    {
        config(['api_response.is_module_available' => false]);

        $this->mockRequestModule('admin');

        $result = ModuleResolver::resolve(null);

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_request_module_when_modules_are_enabled_and_prefix_is_null(): void
    {
        config(['api_response.is_module_available' => true]);

        $this->mockRequestModule('admin');

        $result = ModuleResolver::resolve(null);

        $this->assertSame('admin', $result);
    }

    #[Test]
    public function it_returns_null_when_modules_are_enabled_but_request_has_no_module(): void
    {
        config(['api_response.is_module_available' => true]);

        $this->mockRequestModule(null);

        $result = ModuleResolver::resolve(null);

        $this->assertNull($result);
    }

    /**
     * Injects a macro into the request:
     * - creates a Request with a random URI close to the real one
     * - adds an apiModule() macro to it with the given value
     * - registers the request as the container's 'request' instance
     */
    private function mockRequestModule(?string $module): void
    {
        $request = Request::create('/api/test', 'GET');

        $request::macro('apiModule', fn () => $module);

        $this->app->instance('request', $request);
    }
}
