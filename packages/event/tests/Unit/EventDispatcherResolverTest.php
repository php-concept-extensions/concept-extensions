<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Event\Support\EventDispatcherResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherResolverTest extends TestCase
{
    public function testOptionalReturnsNullWhenContainerHasNoBinding(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $this->assertNull(EventDispatcherResolver::optional($container));
    }

    public function testOptionalReturnsNullWhenBindingHasWrongType(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn(new \stdClass());

        $this->assertNull(EventDispatcherResolver::optional($container));
    }

    public function testOptionalReturnsDispatcherWhenBindingIsValid(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($dispatcher);

        $this->assertSame($dispatcher, EventDispatcherResolver::optional($container));
    }
}
