<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Support\Resolvers\DTO;

use PHPUnit\Framework\TestCase;
use Src83\LaravelApiResponse\Support\Resolvers\DTO\ResolvedMessage;

final class ResolvedMessageTest extends TestCase
{
    public function test_it_holds_passed_values(): void
    {
        $dto = new ResolvedMessage(
            messageKey: 'user.created',
            guiMessage: 'User successfully created',
            module: 'user',
            baseKey: 'created',
        );

        $this->assertSame('user.created', $dto->messageKey);
        $this->assertSame('User successfully created', $dto->guiMessage);
        $this->assertSame('user', $dto->module);
        $this->assertSame('created', $dto->baseKey);
    }

    public function test_null_values_are_supported(): void
    {
        $dto = new ResolvedMessage(
            messageKey: 'user.updated',
            guiMessage: null,
            module: null,
            baseKey: null,
        );

        $this->assertSame('user.updated', $dto->messageKey);
        $this->assertNull($dto->guiMessage);
        $this->assertNull($dto->module);
        $this->assertNull($dto->baseKey);
    }
}
