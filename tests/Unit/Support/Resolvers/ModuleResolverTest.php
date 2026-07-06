<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Support\Resolvers;

use Illuminate\Http\Request;
use Mockery;
use Src83\LaravelApiResponse\Support\Resolvers\ModuleResolver;
use Src83\LaravelApiResponse\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
     * Инжектим макрос в запрос:
     * - создаём Request с рандомным URI, близким к реальному
     * - добавляем к нему macro apiModule() с заданным значением
     * - итоговый запрос кладём в контейнер чтобы он отдавал нужный request
     */
    private function mockRequestModule(?string $module): void
    {
        $request = Request::create('/api/test', 'GET');

        $request::macro('apiModule', fn () => $module);

        $this->app->instance('request', $request);
    }
}
