<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\ErrorHandlerWhoops\Handlers\DeferredDebugHttpHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Whoops\Handler\Handler;
use Whoops\Handler\HandlerInterface;
use Whoops\Run;

final class DeferredDebugHttpHandlerTest extends TestCase
{
    public function testReturnsDoneWhenFactoryReturnsNull(): void
    {
        $handler = new DeferredDebugHttpHandler(static fn(): null => null);
        $handler->setException(new RuntimeException('boom'));

        $this->assertSame(Handler::DONE, $handler->handle());
    }

    public function testDelegatesToFactoryHandler(): void
    {
        $inner = new class extends Handler {
            public bool $called = false;

            public function handle(): int
            {
                $this->called = true;

                return Handler::QUIT;
            }
        };

        $handler = new DeferredDebugHttpHandler(static fn(): HandlerInterface => $inner);
        $handler->setException(new RuntimeException('boom'));
        $handler->setRun(new Run());
        $handler->setInspector(new \Whoops\Exception\Inspector(new RuntimeException('boom')));

        $this->assertSame(Handler::QUIT, $handler->handle());
        $this->assertTrue($inner->called);
    }
}
