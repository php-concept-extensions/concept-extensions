<?php declare(strict_types=1);

namespace Concept\Extensions\Config;

use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\Event\Events\ExtensionAwakened;
use Concept\Extensions\Event\Support\EventDispatcherResolver;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

final class ConfigServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    private const string EXTENSION_NAME = 'config';

    public function __construct(
        private readonly string $root,
        private readonly string $configDirectory,
    ) {}

    public function provides(string $id): bool
    {
        return $id === ConfigInterface::class;
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
        $container = $this->getContainer();
        $config = (new ConfigLoader($this->root, $this->configDirectory))->load();

        $container->add(ConfigInterface::class, $config)->setShared(true);

        EventDispatcherResolver::optional($container)?->dispatch(new ExtensionAwakened(
            extensionName: self::EXTENSION_NAME,
            anchorId: ConfigInterface::class,
        ));
    }
}
