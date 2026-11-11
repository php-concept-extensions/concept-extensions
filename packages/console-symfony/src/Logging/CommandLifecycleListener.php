<?php declare(strict_types=1);

namespace Concept\Extensions\ConsoleSymfony\Logging;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Logs command start / finish / failure for ops visibility (cron, CLI assemblies).
 */
final class CommandLifecycleListener implements EventSubscriberInterface
{
    private const string MSG_STARTED = 'Command started: {command}';
    private const string MSG_FINISHED = 'Command finished: {command}';
    private const string MSG_FAILED = 'Command failed: {command}';

    private float|null $startedAt = null;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
            ConsoleEvents::TERMINATE => 'onTerminate',
            ConsoleEvents::ERROR => 'onError',
        ];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        $this->startedAt = hrtime(true);

        $this->logger->info(self::MSG_STARTED, [
            'command' => self::commandLabel($event),
        ]);
    }

    public function onTerminate(ConsoleTerminateEvent $event): void
    {
        $context = [
            'command' => self::commandLabel($event),
            'exit_code' => $event->getExitCode(),
        ];

        if ($this->startedAt !== null) {
            $context['duration_ms'] = (int) round((hrtime(true) - $this->startedAt) / 1_000_000);
            $this->startedAt = null;
        }

        $this->logger->info(self::MSG_FINISHED, $context);
    }

    public function onError(ConsoleErrorEvent $event): void
    {
        $error = $event->getError();

        $this->logger->error(self::MSG_FAILED, [
            'command' => self::commandLabel($event),
            'message' => $error->getMessage(),
            'exception' => $error,
        ]);
    }

    private static function commandLabel(ConsoleEvent $event): string
    {
        $commandName = $event->getCommand()?->getName();
        $inputString = (string) $event->getInput();

        if ($commandName === null || $commandName === '') {
            return $inputString !== '' ? $inputString : 'unknown';
        }

        if ($inputString === '') {
            return $commandName;
        }

        return str_replace(["'$commandName'", "\"$commandName\""], $commandName, $inputString);
    }
}
