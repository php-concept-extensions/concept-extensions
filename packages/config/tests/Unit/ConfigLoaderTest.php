<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Config\ConfigLoader;
use PHPUnit\Framework\TestCase;
use Tests\Support\EnvIsolation;
use Tests\Support\TempProject;

final class ConfigLoaderTest extends TestCase
{
    private const array ENV_KEYS = ['APP_ENV', 'APP_DEBUG', 'DB_HOST', 'DB_PORT'];

    private EnvIsolation $envIsolation;

    private ?TempProject $project = null;

    protected function setUp(): void
    {
        $this->envIsolation = new EnvIsolation();
        $this->envIsolation->clear(self::ENV_KEYS);
    }

    protected function tearDown(): void
    {
        $this->envIsolation->restore();
        $this->project?->dispose();
        $this->project = null;
    }

    public function testLoadReturnsConfigFromPhpFiles(): void
    {
        $project = $this->project();
        $project->writeConfig('app.php', <<<'PHP'
            <?php declare(strict_types=1);
            return [
                'app' => [
                    'name' => 'Concept',
                    'debug' => false,
                ],
            ];
            PHP);

        $config = $this->loader($project)->load();

        $this->assertSame('Concept', $config->getString('app.name'));
        $this->assertFalse($config->getBool('app.debug'));
    }

    public function testLoadWorksWithoutEnvFile(): void
    {
        $project = $this->project();
        $project->writeConfig('db.php', <<<'PHP'
            <?php declare(strict_types=1);
            return [
                'db' => [
                    'host' => 'mysql',
                ],
            ];
            PHP);

        $config = $this->loader($project)->load();

        $this->assertSame('mysql', $config->getString('db.host'));
    }

    public function testLoadMergesEnvSpecificOverlayDirectory(): void
    {
        $project = $this->project();
        $project->writeConfig('app.php', <<<'PHP'
            <?php declare(strict_types=1);
            return [
                'app' => [
                    'name' => 'Base',
                    'debug' => false,
                ],
            ];
            PHP);
        $project->writeConfig('dev/app.php', <<<'PHP'
            <?php declare(strict_types=1);
            return [
                'app' => [
                    'debug' => true,
                ],
            ];
            PHP);
        $project->writeEnv("APP_ENV=dev\n");

        $config = $this->loader($project)->load();

        $this->assertSame('Base', $config->getString('app.name'));
        $this->assertTrue($config->getBool('app.debug'));
    }

    public function testLoadSkipsOverlayWhenEnvDirectoryMissing(): void
    {
        $project = $this->project();
        $project->writeConfig('app.php', <<<'PHP'
            <?php declare(strict_types=1);
            return [
                'app' => [
                    'debug' => false,
                ],
            ];
            PHP);
        $project->writeEnv("APP_ENV=staging\n");

        $config = $this->loader($project)->load();

        $this->assertFalse($config->getBool('app.debug'));
    }

    public function testLoadMapsEnvKeysToDotNotation(): void
    {
        $project = $this->project();
        $project->writeConfig('app.php', <<<'PHP'
            <?php declare(strict_types=1);
            return ['app' => ['debug' => false]];
            PHP);
        $project->writeEnv(<<<'ENV'
            APP_DEBUG=true
            DB_HOST=127.0.0.1
            DB_PORT=3306
            ENV
        );

        $config = $this->loader($project)->load();

        $this->assertTrue($config->getBool('app.debug'));
        $this->assertSame('127.0.0.1', $config->getString('db.host'));
        $this->assertSame('3306', $config->getString('db.port'));
    }

    public function testEnvValuesOverridePhpConfigAndOverlay(): void
    {
        $project = $this->project();
        $project->writeConfig('app.php', <<<'PHP'
            <?php declare(strict_types=1);
            return [
                'app' => [
                    'debug' => false,
                ],
            ];
            PHP);
        $project->writeConfig('dev/app.php', <<<'PHP'
            <?php declare(strict_types=1);
            return [
                'app' => [
                    'debug' => true,
                ],
            ];
            PHP);
        $project->writeEnv(<<<'ENV'
            APP_ENV=dev
            APP_DEBUG=false
            ENV
        );

        $config = $this->loader($project)->load();

        $this->assertFalse($config->getBool('app.debug'));
    }

    public function testLoadMergesMultiplePhpConfigFiles(): void
    {
        $project = $this->project();
        $project->writeConfig('app.php', <<<'PHP'
            <?php declare(strict_types=1);
            return ['app' => ['name' => 'Concept']];
            PHP);
        $project->writeConfig('log.php', <<<'PHP'
            <?php declare(strict_types=1);
            return ['log' => ['file' => 'app.log']];
            PHP);

        $config = $this->loader($project)->load();

        $this->assertSame('Concept', $config->getString('app.name'));
        $this->assertSame('app.log', $config->getString('log.file'));
    }

    private function project(): TempProject
    {
        return $this->project ??= new TempProject();
    }

    private function loader(TempProject $project): ConfigLoader
    {
        return new ConfigLoader($project->root, $project->configDirectory);
    }
}
