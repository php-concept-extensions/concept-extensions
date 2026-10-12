<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Telemetry\TelemetryCollector;
use PHPUnit\Framework\TestCase;

final class TelemetryCollectorTest extends TestCase
{
    private TelemetryCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new TelemetryCollector();
    }

    public function testStartAndFinishLifecycle(): void
    {
        $id = $this->collector->start('http', ['route' => '/']);

        $this->assertNotSame('', $id);
        $this->collector->finish('http', $id);

        $items = $this->collector->items('http');
        $this->assertCount(1, $items);
        $this->assertNotNull($items[$id]->getFinishedAt());
    }

    public function testFinishOnUnknownIdIsNoOp(): void
    {
        $this->collector->finish('http', 'missing');

        $this->assertSame([], $this->collector->items('http'));
    }

    public function testRecordCreatesFinishedItem(): void
    {
        $id = $this->collector->record('sql', ['time' => 1.2], 1.2, 5.0);

        $item = $this->collector->items('sql')[$id];
        $this->assertSame(1.2, $item->getDuration());
        $this->assertSame(5.0, $item->getStartedAt());
    }

    public function testMarkCreatesShortLivedItem(): void
    {
        $this->collector->mark('boot', 'providers');

        $items = $this->collector->items('boot');
        $this->assertCount(1, $items);
        $this->assertSame('providers', reset($items)->getContext()['name']);
    }

    public function testResetClearsItems(): void
    {
        $this->collector->record('event');

        $this->collector->reset();

        $this->assertSame([], $this->collector->items());
    }

    public function testToArrayGroupsByEventName(): void
    {
        $this->collector->record('http', ['status' => 200]);

        $payload = $this->collector->toArray();

        $this->assertArrayHasKey('http', $payload);
        $this->assertSame('http', reset($payload['http'])['name']);
    }
}
