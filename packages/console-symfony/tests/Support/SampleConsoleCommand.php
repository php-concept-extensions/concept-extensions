<?php declare(strict_types=1);

namespace Tests\Support;

use Symfony\Component\Console\Command\Command;

final class SampleConsoleCommand extends Command
{
    public const string COMMAND_NAME = 'sample:run';

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}
