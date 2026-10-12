<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Telemetry\Handlers\TelemetryLogHandler;
use Concept\Extensions\Telemetry\TelemetryCollector;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

final class TelemetryLogHandlerTest extends TestCase
{
    public function testHandleRecordsTelemetryForMatchingLevel(): void
    {
        $collector = new TelemetryCollector();
        $handler = new TelemetryLogHandler($collector, 'logs');
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'hello',
            context: ['key' => 'value'],
            extra: [],
        );

        $handler->handle($record);

        $items = $collector->items('logs');
        $this->assertCount(1, $items);
        $item = reset($items);
        $this->assertSame('hello', $item->getContext()['message']);
        $this->assertSame('info', $item->getContext()['level']);
    }

    public function testHandleIgnoresRecordsBelowConfiguredLevel(): void
    {
        $collector = new TelemetryCollector();
        $handler = new TelemetryLogHandler($collector, 'logs', Level::Error);
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'ignored',
            context: [],
            extra: [],
        );

        $handler->handle($record);

        $this->assertSame([], $collector->items('logs'));
    }
}
