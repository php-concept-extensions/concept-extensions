<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Config\Config;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Tests\Support\ArrayConfig;

final class ConfigTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        $this->config = new Config(new ArrayConfig([
            'name' => 'concept',
            'port' => '8080',
            'debug' => 'true',
            'off' => 'false',
            'enabled' => 1,
            'disabled' => 0,
            'nullable' => null,
            'routes' => ['web.php', 'api.php', 42],
            'hosts' => ['local' => 'localhost', 'prod' => 'example.com', 'bad' => 123],
            'classes' => [stdClass::class, RuntimeException::class, 'Not\\A\\Class', 7],
            'handlers' => [
                'invalid' => InvalidArgumentException::class,
                'runtime' => RuntimeException::class,
                'broken' => 'Missing\\Handler',
                404 => RuntimeException::class,
            ],
            'nested' => ['a' => 1],
        ]));
    }

    public function testGetHasAndAllDelegateToUnderlyingConfig(): void
    {
        $this->assertTrue($this->config->has('name'));
        $this->assertFalse($this->config->has('missing'));
        $this->assertSame('concept', $this->config->get('name'));
        $this->assertSame('fallback', $this->config->get('missing', 'fallback'));
        $this->assertArrayHasKey('name', $this->config->all());
    }

    public function testGetStringCastsScalarsAndFallsBackForNonScalars(): void
    {
        $this->assertSame('concept', $this->config->getString('name'));
        $this->assertSame('8080', $this->config->getString('port'));
        $this->assertSame('default', $this->config->getString('nested', 'default'));
        $this->assertSame('default', $this->config->getString('missing', 'default'));
    }

    public function testGetIntParsesNumericValues(): void
    {
        $this->assertSame(8080, $this->config->getInt('port'));
        $this->assertSame(99, $this->config->getInt('missing', 99));
        $this->assertSame(5, $this->config->getInt('name', 5));
    }

    public function testGetBoolNormalizesCommonValues(): void
    {
        $this->assertTrue($this->config->getBool('debug'));
        $this->assertFalse($this->config->getBool('off'));
        $this->assertTrue($this->config->getBool('enabled'));
        $this->assertFalse($this->config->getBool('disabled'));
        $this->assertFalse($this->config->getBool('nullable', false));
        $this->assertTrue($this->config->getBool('missing', true));
    }

    public function testGetArrayReturnsArrayOrDefault(): void
    {
        $this->assertSame(['web.php', 'api.php', 42], $this->config->getArray('routes'));
        $this->assertSame(['fallback'], $this->config->getArray('name', ['fallback']));
        $this->assertSame(['fallback'], $this->config->getArray('missing', ['fallback']));
    }

    public function testGetStringListKeepsOnlyStringItems(): void
    {
        $this->assertSame(['web.php', 'api.php'], $this->config->getStringList('routes'));
    }

    public function testGetStringMapKeepsOnlyStringKeysAndValues(): void
    {
        $this->assertSame(
            ['local' => 'localhost', 'prod' => 'example.com'],
            $this->config->getStringMap('hosts'),
        );
    }

    public function testGetClassStringListKeepsOnlyExistingClasses(): void
    {
        $this->assertSame(
            [stdClass::class, RuntimeException::class],
            $this->config->getClassStringList('classes'),
        );
    }

    public function testGetClassListFiltersByBaseClass(): void
    {
        $this->assertSame(
            [RuntimeException::class],
            $this->config->getClassList('classes', RuntimeException::class),
        );
    }

    public function testGetClassMapFiltersByBaseClassAndStringKeys(): void
    {
        $this->assertSame(
            [
                'invalid' => InvalidArgumentException::class,
                'runtime' => RuntimeException::class,
            ],
            $this->config->getClassMap('handlers', Exception::class),
        );
    }
}
