<?php declare(strict_types=1);

namespace Concept\Extensions\ViewPlates;

use Concept\Core\Container\ContainerDependency;
use Concept\Extensions\Event\Events\ExtensionAwakened;
use Concept\Extensions\Event\Support\EventDispatcherResolver;
use Concept\Extensions\View\Contracts\ViewInterface;
use Concept\Extensions\View\Registry\ViewRegistry;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class PlatesViewServiceProvider extends AbstractServiceProvider
{
    private const string EXTENSION_NAME = 'view-plates';
    public const string DEFAULT_EXTENSION = '.php';

    public function __construct(
        private readonly string $viewsPath,
        private readonly string $defaultExtension = self::DEFAULT_EXTENSION,
    ) {}

    public function provides(string $id): bool
    {
        return $id === ViewInterface::class;
    }

    public function register(): void
    {
        $container = $this->getContainer();

        $container->add(ViewInterface::class, function() use ($container): PlatesView {
            EventDispatcherResolver::optional($container)?->dispatch(new ExtensionAwakened(
                extensionName: self::EXTENSION_NAME,
                anchorId: ViewInterface::class,
            ));

            $engine = new Engine($this->viewsPath, ltrim($this->defaultExtension, '.'));

            /** @var ViewRegistry $viewRegistry */
            $viewRegistry = ContainerDependency::get($container, ViewRegistry::class);
            $this->addExtensions($engine, $viewRegistry->extensions()->all());
            $this->addFolders($engine, $viewRegistry->paths()->all());

            return new PlatesView($engine);
        })->setShared(true);
    }

    /**
     * @param array<int, class-string> $extensions
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function addExtensions(Engine $engine, array $extensions): void
    {
        foreach ($extensions as $extensionClass) {
            /** @var ExtensionInterface $extension */
            $extension = $this->getContainer()->get($extensionClass);
            $engine->loadExtension($extension);
        }
    }

    /**
     * @param array<string, string> $namespaces namespace => absolute filesystem path
     */
    private function addFolders(Engine $engine, array $namespaces): void
    {
        foreach ($namespaces as $namespace => $path) {
            $engine->addFolder($namespace, $path, true);
        }
    }
}
