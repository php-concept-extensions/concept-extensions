<?php declare(strict_types=1);

namespace Concept\Extensions\CastingValinor\Routing;

use Concept\Core\Http\Contracts\ArgumentResolverInterface;
use Concept\Extensions\CastingValinor\Contracts\CasterInterface;
use Concept\Extensions\CastingValinor\Exceptions\CastingException;
use Concept\Support\FactoryResolver;
use Closure;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;

final class TypedRouteParameterArgumentResolver implements ArgumentResolverInterface
{
    private ?CasterInterface $caster = null;

    /** @param Closure(): CasterInterface $casterFactory */
    public function __construct(
        private readonly Closure $casterFactory,
    ) {}

    public function supports(ReflectionParameter $parameter, array $vars): bool
    {
        if (!array_key_exists($parameter->getName(), $vars)) {
            return false;
        }

        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType) {
            return false;
        }

        if ($type->isBuiltin()) {
            return true;
        }

        $typeName = $type->getName();

        return $typeName !== ServerRequestInterface::class
            && !is_subclass_of($typeName, ServerRequestInterface::class);
    }

    public function resolve(ReflectionParameter $parameter, ServerRequestInterface $request, array $vars): mixed
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType) {
            throw new CastingException('mixed');
        }

        return $this->caster()->cast($vars[$parameter->getName()], $type->getName());
    }

    private function caster(): CasterInterface
    {
        if ($this->caster === null) {
            $casterFactory = $this->casterFactory;
            $this->caster = FactoryResolver::required($casterFactory, CasterInterface::class, 'Caster factory result');
        }

        return $this->caster;
    }
}
