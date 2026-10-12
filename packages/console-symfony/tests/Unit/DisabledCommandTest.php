<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\ConsoleSymfony\DisabledCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\SampleConsoleCommand;

final class DisabledCommandTest extends TestCase
{
    public function testUsesCommandNameFromTargetClass(): void
    {
        $command = new DisabledCommand(SampleConsoleCommand::class, 'missing dependency');

        $this->assertSame(SampleConsoleCommand::COMMAND_NAME, $command->getName());
    }

    public function testUsesUnknownNameWhenTargetClassMissing(): void
    {
        $command = new DisabledCommand('Missing\\Command\\Class', 'error');

        $this->assertSame('unknown', $command->getName());
    }

    public function testExecuteReturnsFailureAndPrintsDetails(): void
    {
        $command = new DisabledCommand(SampleConsoleCommand::class, 'db extension missing');
        $tester = new CommandTester($command);

        $status = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString(SampleConsoleCommand::class, $tester->getDisplay());
        $this->assertStringContainsString('db extension missing', $tester->getDisplay());
    }
}
