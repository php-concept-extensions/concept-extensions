<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Telemetry\TelemetryItem;
use PHPUnit\Framework\TestCase;

final class TelemetryItemTest extends TestCase
{
    public function testConstructorWithDurationFinishesImmediately(): void
    {
        $item = new TelemetryItem('query', ['sql' => 'select 1'], 0.5, 100.0);

        $this->assertSame(100.5, $item->getFinishedAt());
        $this->assertSame(0.5, $item->getDuration());
    }

    public function testFinishIsIdempotent(): void
    {
        $item = new TelemetryItem('event', [], null, 10.0);
        $item->finish();
        $firstFinishedAt = $item->getFinishedAt();

        $item->finish();

        $this->assertSame($firstFinishedAt, $item->getFinishedAt());
    }

    public function testToArrayContainsExpectedKeys(): void
    {
        $item = new TelemetryItem('boot', ['step' => 'config'], 0.1, 1.0);

        $this->assertSame([
            'name' => 'boot',
            'context' => ['step' => 'config'],
            'started_at' => 1.0,
            'finished_at' => 1.1,
            'duration' => 0.1,
        ], $item->toArray());
    }
}
