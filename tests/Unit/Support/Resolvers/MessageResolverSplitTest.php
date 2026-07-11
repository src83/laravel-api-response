<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Unit\Support\Resolvers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src83\LaravelApiResponse\Support\Resolvers\MessageResolver;

final class MessageResolverSplitTest extends TestCase
{
    #[Test]
    public function it_returns_null_prefix_for_null_key(): void
    {
        [$prefix, $baseKey, $ignoreDefaultModule] = MessageResolver::split(null);

        $this->assertNull($prefix);
        $this->assertNull($baseKey);
        $this->assertFalse($ignoreDefaultModule);
    }

    #[Test]
    public function it_returns_null_prefix_for_simple_key(): void
    {
        [$prefix, $baseKey, $ignoreDefaultModule] = MessageResolver::split('created');

        $this->assertNull($prefix);
        $this->assertSame('created', $baseKey);
        $this->assertFalse($ignoreDefaultModule);
    }

    #[Test]
    public function it_splits_composite_key_by_first_dot(): void
    {
        [$prefix, $baseKey, $ignoreDefaultModule] = MessageResolver::split('users.created');

        $this->assertSame('users', $prefix);
        $this->assertSame('created', $baseKey);
        $this->assertFalse($ignoreDefaultModule);
    }

    #[Test]
    public function it_splits_only_by_first_dot_when_multiple_dots_present(): void
    {
        [$prefix, $baseKey, $ignoreDefaultModule] = MessageResolver::split('users.sub.created');

        $this->assertSame('users', $prefix);
        $this->assertSame('sub.created', $baseKey);
        $this->assertFalse($ignoreDefaultModule);
    }

    #[Test]
    public function it_handles_leading_dot(): void
    {
        [$prefix, $baseKey, $ignoreDefaultModule] = MessageResolver::split('.created');

        $this->assertNull($prefix);
        $this->assertSame('created', $baseKey);
        $this->assertTrue($ignoreDefaultModule);
    }

    #[Test]
    public function it_handles_trailing_dot(): void
    {
        [$prefix, $baseKey, $ignoreDefaultModule] = MessageResolver::split('users.');

        $this->assertSame('users', $prefix);
        $this->assertNull($baseKey);
        $this->assertFalse($ignoreDefaultModule);
    }
}
