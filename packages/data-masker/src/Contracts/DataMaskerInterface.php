<?php declare(strict_types=1);

namespace Concept\Extensions\DataMasker\Contracts;

interface DataMaskerInterface
{
    /**
     * @template T
     * @param T $data
     * @return (T is string ? string : (T is array ? array<mixed> : (T is object ? object : T)))
     */
    public function mask(mixed $data): mixed;

    public function clearRules(): void;
}
