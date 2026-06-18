<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Support\Resolvers;

use Illuminate\Http\Request;
use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Support\Resolvers\MessageResolver;
use Src83\LaravelApiResponse\Tests\TestCase;

final class MessageResolverTest extends TestCase
{
    /** @test */
    public function it_resolves_module_for_simple_key_to_null_when_modules_not_allowed(): void
    {
        config(['api.is_module_available' => false]);

        $resolved = MessageResolver::resolve(MessageKeyEnum::CREATED, null);

        $this->assertSame('created', $resolved->messageKey);
        $this->assertNull($resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }

    /** @test */
    public function it_resolves_module_for_composite_key_from_prefix_when_modules_not_allowed(): void
    {
        config(['api.is_module_available' => false]);

        $resolved = MessageResolver::resolve('users.created', null);

        $this->assertSame('users.created', $resolved->messageKey);
        $this->assertSame('users', $resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }

    /** @test */
    public function it_resolves_module_for_simple_key_from_request_when_modules_allowed(): void
    {
        config(['api.is_module_available' => true]);

        $request = Request::create('/api/something', 'GET');
        $request::macro('apiModule', fn () => 'auth');
        $this->app->instance('request', $request);

        $resolved = MessageResolver::resolve(MessageKeyEnum::CREATED, null);

        $this->assertSame('auth.created', $resolved->messageKey);
        $this->assertSame('auth', $resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }

    /** @test */
    public function it_resolves_module_for_composite_key_from_prefix_when_modules_allowed(): void
    {
        config(['api.is_module_available' => true]);

        $request = Request::create('/api/something', 'GET');
        $request::macro('apiModule', fn () => 'auth');
        $this->app->instance('request', $request);

        $resolved = MessageResolver::resolve('users.created', null);

        $this->assertSame('users.created', $resolved->messageKey);
        $this->assertSame('users', $resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }

    /** @test */
    public function it_does_not_override_gui_message_when_user_provided_it(): void
    {
        $guiMessage = 'Everything is shiny captain!';

        $resolved = MessageResolver::resolve(MessageKeyEnum::UPDATED, $guiMessage);

        $this->assertSame('updated', $resolved->messageKey);
        $this->assertSame($guiMessage, $resolved->guiMessage);
    }

    /** @test */
    public function it_handles_empty_localization(): void
    {
        $resolved = MessageResolver::resolve('rechecked', null);

        $this->assertSame('no_translation', $resolved->guiMessage);
    }

    /** @test */
    public function it_removes_default_module_when_message_key_starts_with_dot(): void
    {
        config(['api.is_module_available' => true]);

        $request = Request::create('/api/something', 'GET');
        $request::macro('apiModule', fn () => 'auth');
        $this->app->instance('request', $request);

        $resolved = MessageResolver::resolve('.created', null);

        $this->assertSame('created', $resolved->messageKey);
        $this->assertNull($resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }
}
