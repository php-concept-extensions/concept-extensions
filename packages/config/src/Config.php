<?php declare(strict_types=1);

namespace Concept\Extensions\Config;

use Concept\Extensions\Config\Contracts\ConfigInterface;
use Noodlehaus\ConfigInterface as NoodlehausConfigInterface;

final class Config implements ConfigInterface
{
    public function __construct(
        private readonly NoodlehausConfigInterface $noodlehausConfig,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->noodlehausConfig->get($key, $default);
    }

    public function has(string $key): bool
    {
        return $this->noodlehausConfig->has($key);
    }

    public function all(): array
    {
        return $this->noodlehausConfig->all();
    }

    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        if (!is_scalar($value)) {
            return $default;
        }

        return (string) $value;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        if (!is_numeric($value)) {
            return $default;
        }

        return (int) $value;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);
        if (!is_array($value)) {
            return $default;
        }

        return $value;
    }

    /**
     * @param array<mixed> $default
     * @return list<string>
     */
    public function getStringList(string $key, array $default = []): array
    {
        $items = [];
        foreach ($this->getArray($key, $default) as $item) {
            if (is_string($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param array<mixed> $default
     * @return array<string, string>
     */
    public function getStringMap(string $key, array $default = []): array
    {
        $map = [];
        foreach ($this->getArray($key, $default) as $mapKey => $value) {
            if (is_string($mapKey) && is_string($value)) {
                $map[$mapKey] = $value;
            }
        }

        return $map;
    }

    /**
     * @param array<mixed> $default
     * @return list<class-string>
     */
    public function getClassStringList(string $key, array $default = []): array
    {
        $items = [];
        foreach ($this->getArray($key, $default) as $item) {
            if (is_string($item) && $item !== '' && class_exists($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<mixed> $default
     * @return list<class-string<T>>
     */
    public function getClassList(string $key, string $class, array $default = []): array
    {
        $items = [];
        foreach ($this->getArray($key, $default) as $item) {
            if (is_string($item) && $item !== '' && is_a($item, $class, true)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<mixed> $default
     * @return array<string, class-string<T>>
     */
    public function getClassMap(string $key, string $class, array $default = []): array
    {
        $map = [];
        foreach ($this->getArray($key, $default) as $mapKey => $value) {
            if (is_string($mapKey) && is_string($value) && $value !== '' && is_a($value, $class, true)) {
                $map[$mapKey] = $value;
            }
        }

        return $map;
    }
}
