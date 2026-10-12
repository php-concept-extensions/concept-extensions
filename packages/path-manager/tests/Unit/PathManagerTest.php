<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\PathManager\PathManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PathManagerTest extends TestCase
{
    private const string ROOT = '/var/www/project';

    /** @var array<string, string> */
    private const array PATH_MAP = [
        'logs' => 'storage/logs',
        'config' => 'config',
    ];

    private PathManager $manager;

    protected function setUp(): void
    {
        $this->manager = new PathManager(self::ROOT, self::PATH_MAP);
    }

    public function testRootReturnsProjectPathWithOptionalSuffix(): void
    {
        $this->assertSame('/var/www/project/', $this->manager->root());
        $this->assertSame('/var/www/project/public/index.php', $this->manager->root('public/index.php'));
    }

    public function testHasAndGetKeyValue(): void
    {
        $this->assertTrue($this->manager->has('logs'));
        $this->assertFalse($this->manager->has('missing'));
        $this->assertSame('storage/logs', $this->manager->getKeyValue('logs'));
        $this->assertNull($this->manager->getKeyValue('missing'));
    }

    public function testGetResolvesMappedPath(): void
    {
        $this->assertSame('/var/www/project/storage/logs', $this->manager->get('logs'));
        $this->assertSame(
            '/var/www/project/storage/logs/app.log',
            $this->manager->get('logs', 'app.log'),
        );
    }

    public function testGetThrowsForUnknownKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->manager->get('unknown');
    }

    public function testGetRelativeReturnsPathRelativeToProjectRoot(): void
    {
        $this->assertSame('storage/logs/app.log', $this->manager->getRelative('logs', 'app.log'));
    }

    public function testToRelativeConvertsAbsolutePathUnderRoot(): void
    {
        $this->assertSame(
            'config/app.php',
            $this->manager->toRelative('/var/www/project/config/app.php'),
        );
    }

    public function testToRelativeReturnsAlreadyRelativePath(): void
    {
        $this->assertSame('config/app.php', $this->manager->toRelative('config/app.php'));
    }

    public function testToRelativeNormalizesBackslashes(): void
    {
        $this->assertSame(
            'config/app.php',
            $this->manager->toRelative('\\var\\www\\project\\config\\app.php'),
        );
    }

    public function testToRelativeThrowsWhenPathIsOutsideProjectRoot(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('outside the project root');

        $this->manager->toRelative('/etc/passwd');
    }

    public function testRootListResolvesEachPath(): void
    {
        $this->assertSame(
            ['/var/www/project/config', '/var/www/project/routes/web.php'],
            $this->manager->rootList(['config', 'routes/web.php']),
        );
    }

    public function testRootMapResolvesKeyedPaths(): void
    {
        $this->assertSame(
            [
                'app' => '/var/www/project/config/app.php',
                'routes' => '/var/www/project/routes',
            ],
            $this->manager->rootMap([
                'app' => 'config/app.php',
                'routes' => 'routes',
            ]),
        );
    }
}
