<?php declare(strict_types=1);

namespace Concept\Extensions\View\Support;

use Concept\Core\Http\Contracts\RequestContextInterface;
use Concept\Extensions\View\Registry\ViewRouteNamespaceRegistry;
use RuntimeException;

final class ViewRouteNamespaceResolver
{
    private const string ERR_NO_REQUEST = 'HTTP request is not available in RequestContext.';

    public function __construct(
        private readonly ViewRouteNamespaceRegistry $routeNamespaceRegistry,
        private readonly RequestContextInterface $requestContext,
    ) {}

    public function resolve(): ?string
    {
        $request = $this->requestContext->current()
            ?? throw new RuntimeException(self::ERR_NO_REQUEST);

        $path = '/' . ltrim($request->getUri()->getPath(), '/');
        $map = $this->routeNamespaceRegistry->all();
        uksort($map, fn(string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($map as $prefix => $namespace) {
            if (str_starts_with($path, $prefix)) {
                return $namespace;
            }
        }

        return $map !== [] ? (string) reset($map) : null;
    }
}
