<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\CastingValinor\Caster;
use Concept\Extensions\CastingValinor\Exceptions\CastingException;
use PHPUnit\Framework\TestCase;
use Tests\Support\SampleUserDto;

final class CasterTest extends TestCase
{
    private Caster $caster;

    protected function setUp(): void
    {
        $this->caster = new Caster();
    }

    public function testCastsScalarTypes(): void
    {
        $this->assertSame(42, $this->caster->cast('42', 'int'));
        $this->assertSame('value', $this->caster->cast('value', 'string'));
        $this->assertTrue($this->caster->cast('1', 'bool'));
        $this->assertFalse($this->caster->cast('0', 'bool'));
    }

    public function testCastsArrayToDto(): void
    {
        $dto = $this->caster->cast(
            ['name' => 'Alice', 'age' => '30'],
            SampleUserDto::class,
        );

        $this->assertInstanceOf(SampleUserDto::class, $dto);
        $this->assertSame('Alice', $dto->name);
        $this->assertSame(30, $dto->age);
    }

    public function testCastThrowsCastingExceptionForInvalidValue(): void
    {
        $this->expectException(CastingException::class);
        $this->expectExceptionMessage('Failed to cast provided data to type: int');

        $this->caster->cast('not-a-number', 'int');
    }
}
