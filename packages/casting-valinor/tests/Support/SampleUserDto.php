<?php declare(strict_types=1);

namespace Tests\Support;

use Concept\Extensions\CastingValinor\Dto\Dto;

final class SampleUserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {}
}
