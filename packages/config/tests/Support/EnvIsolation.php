<?php declare(strict_types=1);

namespace Tests\Support;

final class EnvIsolation
{
    /** @var array<string, array{getenv: string|false, env: mixed, server: mixed}> */
    private array $snapshot = [];

    /**
     * @param list<string> $keys
     */
    public function clear(array $keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->snapshot)) {
                $this->snapshot[$key] = [
                    'getenv' => getenv($key),
                    'env' => $_ENV[$key] ?? null,
                    'server' => $_SERVER[$key] ?? null,
                ];
            }

            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }

    public function restore(): void
    {
        foreach ($this->snapshot as $key => $state) {
            if ($state['getenv'] === false) {
                putenv($key);
            } else {
                putenv("{$key}={$state['getenv']}");
            }

            if ($state['env'] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $state['env'];
            }

            if ($state['server'] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $state['server'];
            }
        }

        $this->snapshot = [];
    }
}
