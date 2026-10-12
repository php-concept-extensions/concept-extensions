<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Http\Protocol\HttpHeader;
use Concept\Extensions\Http\Protocol\HttpValue;
use Concept\Extensions\Json\Middleware\ParseJsonBodyMiddleware;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use Tests\Support\RecordingRequestHandler;

final class ParseJsonBodyMiddlewareTest extends TestCase
{
    private ParseJsonBodyMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new ParseJsonBodyMiddleware();
    }

    public function testProcessLeavesRequestUnchangedForNonJsonContentType(): void
    {
        $handler = new RecordingRequestHandler();
        $request = $this->requestWithBody('name=Alice', 'application/x-www-form-urlencoded');

        $this->middleware->process($request, $handler);

        $this->assertNull($handler->request?->getParsedBody());
    }

    public function testProcessParsesValidJsonBody(): void
    {
        $handler = new RecordingRequestHandler();
        $request = $this->requestWithBody(
            '{"name":"Alice","age":30}',
            HttpValue::JSON,
        );

        $this->middleware->process($request, $handler);

        $this->assertSame(['name' => 'Alice', 'age' => 30], $handler->request?->getParsedBody());
    }

    public function testProcessParsesJsonBodyWhenContentTypeIncludesCharset(): void
    {
        $handler = new RecordingRequestHandler();
        $request = $this->requestWithBody(
            '{"ok":true}',
            HttpValue::JSON . '; charset=utf-8',
        );

        $this->middleware->process($request, $handler);

        $this->assertSame(['ok' => true], $handler->request?->getParsedBody());
    }

    public function testProcessLeavesRequestUnchangedForInvalidJson(): void
    {
        $handler = new RecordingRequestHandler();
        $request = $this->requestWithBody('{invalid', HttpValue::JSON);

        $this->middleware->process($request, $handler);

        $this->assertNull($handler->request?->getParsedBody());
    }

    private function requestWithBody(string $body, string $contentType): ServerRequest
    {
        $stream = new Stream('php://memory', 'wb+');
        $stream->write($body);
        $stream->rewind();

        return (new ServerRequest())
            ->withHeader(HttpHeader::CONTENT_TYPE, $contentType)
            ->withBody($stream);
    }
}
