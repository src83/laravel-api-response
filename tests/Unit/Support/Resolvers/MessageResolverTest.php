<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Support\Resolvers;

use Illuminate\Http\Request;
use Src83\LaravelApiResponse\Enums\MessageKeyEnum;
use Src83\LaravelApiResponse\Support\Resolvers\MessageResolver;
use Src83\LaravelApiResponse\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class MessageResolverTest extends TestCase
{
    #[Test]
    public function it_resolves_module_for_simple_key_to_null_when_modules_not_allowed(): void
    {
        config(['api.is_module_available' => false]);

        // given
        $messageKey = MessageKeyEnum::CREATED;

        // when
        $resolved = MessageResolver::resolve($messageKey, null);

        // then
        $this->assertSame('created', $resolved->messageKey);
        $this->assertNull($resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }

    #[Test]
    public function it_resolves_module_for_composite_key_from_prefix_when_modules_not_allowed(): void
    {
        config(['api.is_module_available' => false]);

        // given
        $messageKey = 'users.created';

        // when
        $resolved = MessageResolver::resolve($messageKey, null);

        // then
        $this->assertSame('users.created', $resolved->messageKey);
        $this->assertSame('users', $resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }

    #[Test]
    public function it_resolves_module_for_simple_key_from_request_when_modules_allowed(): void
    {
        config(['api.is_module_available' => true]);

        // given
        $messageKey = MessageKeyEnum::CREATED;

        $request = Request::create('/api/something', 'GET');
        $request::macro('apiModule', fn () => 'auth');
        $this->app->instance('request', $request);

        // when
        $resolved = MessageResolver::resolve($messageKey, null);

        // then
        $this->assertSame('auth.created', $resolved->messageKey);
        $this->assertSame('auth', $resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }

    #[Test]
    public function it_resolves_module_for_composite_key_from_prefix_when_modules_allowed(): void
    {
        config(['api.is_module_available' => true]);

        // given
        $messageKey = 'users.created';

        $request = Request::create('/api/something', 'GET');
        $request::macro('apiModule', fn () => 'auth');
        $this->app->instance('request', $request);

        // when
        $resolved = MessageResolver::resolve($messageKey, null);

        // then
        $this->assertSame('users.created', $resolved->messageKey);
        $this->assertSame('users', $resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }

    #[Test]
    public function it_does_not_override_gui_message_when_user_provided_it(): void
    {
        // given
        $messageKey = MessageKeyEnum::UPDATED;
        $guiMessage = 'Everything is shiny captain!';

        // when
        $resolved = MessageResolver::resolve($messageKey, $guiMessage);

        // then
        $this->assertSame('updated', $resolved->messageKey);
        $this->assertSame($guiMessage, $resolved->guiMessage);
    }

    #[Test]
    public function it_handles_empty_localization(): void
    {
        // given
        $messageKey = 'rechecked';

        // when
        $resolved = MessageResolver::resolve($messageKey, null);

        // then
        $this->assertSame('no_translation', $resolved->guiMessage);
    }

    #[Test]
    public function it_removes_default_module_when_message_key_starts_with_dot(): void
    {
        config(['api.is_module_available' => true]);

        // given
        $messageKey = '.created';

        $request = Request::create('/api/something', 'GET');
        $request::macro('apiModule', fn () => 'auth');
        $this->app->instance('request', $request);

        // when
        $resolved = MessageResolver::resolve($messageKey, null);

        // then
        $this->assertSame('created', $resolved->messageKey);
        $this->assertNull($resolved->module);
        $this->assertSame('created', $resolved->baseKey);
        $this->assertIsString($resolved->guiMessage);
    }
}
