<?php declare(strict_types=1);

namespace Concept\Support;

use RuntimeException;

final class ContractGuard
{
    /**
     * @template T of object
     * @param class-string<T> $expected
     * @return T
     */
    public static function instanceOf(mixed $value, string $expected, string $subject): object
    {
        if ($value instanceof $expected) {
            return $value;
        }

        throw new RuntimeException(sprintf(
            '%s must be an instance of %s, got %s.',
            $subject,
            $expected,
            self::describe($value),
        ));
    }

    private static function describe(mixed $value): string
    {
        return is_object($value) ? $value::class : get_debug_type($value);
    }
}
