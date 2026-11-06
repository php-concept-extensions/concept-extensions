<?php declare(strict_types=1);

namespace Concept\Extensions\DatabaseEloquent;

use Concept\Core\Container\ContainerDependency;
use Concept\Core\Http\Contracts\RequestContextInterface;
use Illuminate\Pagination\Paginator;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

final class PaginationConfiguratorServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    public function provides(string $id): bool
    {
        return false;
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
        $container = $this->getContainer();

        Paginator::currentPageResolver(function(string $pageName = 'page') use ($container): int {
            $request = ContainerDependency::getOrNull($container, RequestContextInterface::class)?->current();
            if ($request === null) {
                return 1;
            }

            $params = $request->getQueryParams();

            return (int) ($params[$pageName] ?? 1);
        });

        Paginator::currentPathResolver(function() use ($container): string {
            $request = ContainerDependency::getOrNull($container, RequestContextInterface::class)?->current();

            return $request?->getUri()->getPath() ?? '/';
        });
    }
}
