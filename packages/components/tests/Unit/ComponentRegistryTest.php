<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\Components\ComponentRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tests\Support\StubComponent;

final class ComponentRegistryTest extends TestCase
{
    public function testAggregatesComponentCapabilities(): void
    {
        $component = new StubComponent();
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($component);

        $registry = new ComponentRegistry($container, [StubComponent::class]);

        $this->assertSame(['StubProvider'], $registry->providers());
        $this->assertSame(['StubSeeder'], $registry->seeders());
        $this->assertSame(['/migrations/stub'], $registry->migrationPaths());
        $this->assertSame(['StubCommand'], $registry->commands());
        $this->assertSame(['StubExtension'], $registry->viewExtensions());
        $this->assertSame(['stub' => '/views/stub'], $registry->viewPaths());
        $this->assertSame(['/stub' => 'stub'], $registry->viewRouteNamespace());
        $this->assertSame(['/src/assets' => '/public/assets'], $registry->assets());
        $this->assertSame([$component], $registry->all());
    }

    public function testRoutesReturnsExistingRouteFiles(): void
    {
        $routesFile = sys_get_temp_dir() . '/component-routes-' . uniqid('', true) . '.php';
        file_put_contents($routesFile, '<?php');

        try {
            $container = $this->createMock(ContainerInterface::class);
            $container->method('get')->willReturn(new StubComponent(routesFile: $routesFile));

            $registry = new ComponentRegistry($container, [StubComponent::class]);

            $this->assertSame([$routesFile], $registry->routes());
        } finally {
            @unlink($routesFile);
        }
    }

    public function testRoutesThrowsWhenFileMissing(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(new StubComponent(routesFile: '/missing/routes.php'));

        $registry = new ComponentRegistry($container, [StubComponent::class]);

        $this->expectException(InvalidArgumentException::class);
        $registry->routes();
    }

    public function testHasReturnsFalseBeforeComponentsAreLoaded(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $registry = new ComponentRegistry($container, [StubComponent::class]);

        $this->assertFalse($registry->has('stub'));
    }

    public function testHasFindsComponentByNameOrClass(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(new StubComponent());

        $registry = new ComponentRegistry($container, [StubComponent::class]);
        $registry->providers();

        $this->assertTrue($registry->has('stub'));
        $this->assertTrue($registry->has(StubComponent::class));
        $this->assertFalse($registry->has('missing'));
    }
}
