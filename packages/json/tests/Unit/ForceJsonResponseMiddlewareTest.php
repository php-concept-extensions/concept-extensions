<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Http\Protocol\HttpHeader;
use Concept\Extensions\Http\Protocol\HttpValue;
use Concept\Extensions\Json\Middleware\ForceJsonResponseMiddleware;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Support\RecordingRequestHandler;

final class ForceJsonResponseMiddlewareTest extends TestCase
{
    public function testProcessForcesJsonAcceptHeaderAndResponseContentType(): void
    {
        $middleware = new ForceJsonResponseMiddleware();
        $handler = new RecordingRequestHandler();
        $request = new ServerRequest();

        $response = $middleware->process($request, $handler);

        $this->assertSame(HttpValue::JSON, $handler->request?->getHeaderLine(HttpHeader::ACCEPT));
        $this->assertSame(HttpValue::JSON, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
    }

    public function testProcessOverwritesExistingResponseContentType(): void
    {
        $middleware = new ForceJsonResponseMiddleware();
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new Response())->withHeader(HttpHeader::CONTENT_TYPE, 'text/html');
            }
        };

        $response = $middleware->process(new ServerRequest(), $handler);

        $this->assertSame(HttpValue::JSON, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
    }
}
