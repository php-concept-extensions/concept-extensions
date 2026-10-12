<?php declare(strict_types=1);

namespace Concept\Extensions\Config\Contracts;

interface ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    /**
     * @return array<mixed, mixed>
     */
    public function all(): array;

    public function getString(string $key, string $default = ''): string;

    public function getInt(string $key, int $default = 0): int;

    public function getBool(string $key, bool $default = false): bool;

    /**
     * @param string $key
     * @param array<mixed> $default
     * @return array<mixed>
     */
    public function getArray(string $key, array $default = []): array;

    /**
     * @param array<mixed> $default
     * @return list<string>
     */
    public function getStringList(string $key, array $default = []): array;

    /**
     * @param array<mixed> $default
     * @return array<string, string>
     */
    public function getStringMap(string $key, array $default = []): array;

    /**
     * @param array<mixed> $default
     * @return list<class-string>
     */
    public function getClassStringList(string $key, array $default = []): array;

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<mixed> $default
     * @return list<class-string<T>>
     */
    public function getClassList(string $key, string $class, array $default = []): array;

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<mixed> $default
     * @return array<string, class-string<T>>
     */
    public function getClassMap(string $key, string $class, array $default = []): array;
}
