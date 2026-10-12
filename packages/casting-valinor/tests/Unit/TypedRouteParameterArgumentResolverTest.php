<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\CastingValinor\Caster;
use Concept\Extensions\CastingValinor\Routing\TypedRouteParameterArgumentResolver;
use InvalidArgumentException;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionParameter;
use Tests\Support\SampleUserDto;

final class TypedRouteParameterArgumentResolverTest extends TestCase
{
    private TypedRouteParameterArgumentResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new TypedRouteParameterArgumentResolver(
            casterFactory: static fn(): Caster => new Caster(),
        );
    }

    public function testSupportsReturnsTrueForBuiltinRouteParameter(): void
    {
        $parameter = $this->parameterNamed('id', 'int');

        $this->assertTrue($this->resolver->supports($parameter, ['id' => '42']));
    }

    public function testSupportsReturnsTrueForCustomDtoRouteParameter(): void
    {
        $parameter = $this->parameterNamed('user', SampleUserDto::class);

        $this->assertTrue($this->resolver->supports($parameter, ['user' => '{"name":"Alice","age":30}']));
    }

    public function testSupportsReturnsFalseWhenRouteVarIsMissing(): void
    {
        $parameter = $this->parameterNamed('id', 'int');

        $this->assertFalse($this->resolver->supports($parameter, []));
    }

    public function testSupportsReturnsFalseForServerRequestParameter(): void
    {
        $parameter = $this->parameterNamed('request', ServerRequestInterface::class);

        $this->assertFalse($this->resolver->supports($parameter, ['request' => 'ignored']));
    }

    public function testResolveCastsRouteVarToDeclaredType(): void
    {
        $parameter = $this->parameterNamed('id', 'int');
        $request = new ServerRequest();

        $value = $this->resolver->resolve($parameter, $request, ['id' => '42']);

        $this->assertSame(42, $value);
    }

    public function testResolveCastsRouteVarToDto(): void
    {
        $parameter = $this->parameterNamed('user', SampleUserDto::class);
        $request = new ServerRequest();

        $value = $this->resolver->resolve(
            $parameter,
            $request,
            ['user' => ['name' => 'Bob', 'age' => '25']],
        );

        $this->assertInstanceOf(SampleUserDto::class, $value);
        $this->assertSame('Bob', $value->name);
        $this->assertSame(25, $value->age);
    }

    public function testResolveReusesCasterFromFactory(): void
    {
        $calls = 0;
        $resolver = new TypedRouteParameterArgumentResolver(
            casterFactory: static function() use (&$calls): Caster {
                ++$calls;

                return new Caster();
            },
        );

        $parameter = $this->parameterNamed('id', 'int');
        $request = new ServerRequest();
        $vars = ['id' => '1'];

        $resolver->resolve($parameter, $request, $vars);
        $resolver->resolve($parameter, $request, $vars);

        $this->assertSame(1, $calls);
    }

    private function parameterNamed(string $name, string $type): ReflectionParameter
    {
        return match ($name) {
            'id' => (new ReflectionFunction(function (int $id): void {}))->getParameters()[0],
            'user' => (new ReflectionFunction(function (SampleUserDto $user): void {}))->getParameters()[0],
            'request' => (new ReflectionFunction(function (ServerRequestInterface $request): void {}))->getParameters()[0],
            default => throw new InvalidArgumentException("Unknown parameter: {$name}"),
        };
    }
}
