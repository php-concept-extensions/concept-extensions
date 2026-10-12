<?php declare(strict_types=1);

namespace Concept\Support;

use Closure;

final class FactoryResolver
{
    /**
     * @template T of object
     * @param Closure(): mixed $factory
     * @param class-string<T> $expected
     * @return T
     */
    public static function required(Closure $factory, string $expected, string $subject): object
    {
        return ContractGuard::instanceOf($factory(), $expected, $subject);
    }

    /**
     * Optional dependency: absent when there is no factory or the factory
     * resolves to null. A non-null result is still type-guarded.
     *
     * @template T of object
     * @param (Closure(): mixed)|null $factory
     * @param class-string<T> $expected
     * @return T|null
     */
    public static function optional(?Closure $factory, string $expected, string $subject): ?object
    {
        if ($factory === null) {
            return null;
        }

        $value = $factory();
        if ($value === null) {
            return null;
        }

        return ContractGuard::instanceOf($value, $expected, $subject);
    }
}
