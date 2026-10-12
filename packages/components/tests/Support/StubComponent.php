<?php declare(strict_types=1);

namespace Tests\Support;

use Concept\Extensions\Components\Contracts\ComponentInterface;

final class StubComponent implements ComponentInterface
{
    public function __construct(
        private readonly string $componentName = 'stub',
        private readonly ?string $routesFile = null,
    ) {}

    public function name(): string
    {
        return $this->componentName;
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function description(): string
    {
        return 'Stub component';
    }

    public function routes(): ?string
    {
        return $this->routesFile;
    }

    public function providers(): array
    {
        return ['StubProvider'];
    }

    public function viewExtensions(): array
    {
        return ['StubExtension'];
    }

    public function viewPaths(): array
    {
        return ['stub' => '/views/stub'];
    }

    public function viewRouteNamespace(): array
    {
        return ['/stub' => 'stub'];
    }

    public function commands(): array
    {
        return ['StubCommand'];
    }

    public function seeders(): array
    {
        return ['StubSeeder'];
    }

    public function migrationPaths(): array
    {
        return ['/migrations/stub'];
    }

    public function assets(): array
    {
        return ['/src/assets' => '/public/assets'];
    }
}
