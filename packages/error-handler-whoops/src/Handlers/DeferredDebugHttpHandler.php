<?php declare(strict_types=1);

namespace Concept\Extensions\ErrorHandlerWhoops\Handlers;

use Closure;
use Whoops\Handler\Handler;
use Whoops\Handler\HandlerInterface;

/**
 * Invokes a debug-handler factory at exception time (not at Whoops boot),
 * so request-scoped checks (e.g. Accept: JSON) see RequestContext.
 */
final class DeferredDebugHttpHandler extends Handler
{
    /**
     * @param Closure(): ?HandlerInterface $factory
     */
    public function __construct(
        private readonly Closure $factory,
    ) {}

    public function handle(): int
    {
        $handler = ($this->factory)();
        if ($handler === null) {
            return Handler::DONE;
        }

        $handler->setRun($this->getRun());
        $handler->setException($this->getException());
        $handler->setInspector($this->getInspector());

        return $handler->handle();
    }
}
