<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Core\Http\Request\RequestContext;
use Concept\Extensions\View\Registry\ViewRouteNamespaceRegistry;
use Concept\Extensions\View\Support\ViewRouteNamespaceResolver;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ViewRouteNamespaceResolverTest extends TestCase
{
    public function testResolveUsesLongestMatchingPrefix(): void
    {
        $registry = new ViewRouteNamespaceRegistry();
        $registry->append([
            '/admin' => 'admin',
            '/admin/settings' => 'admin-settings',
        ]);
        $context = new RequestContext();
        $context->set((new ServerRequest())->withUri(new Uri('https://example.com/admin/settings/profile')));
        $resolver = new ViewRouteNamespaceResolver($registry, $context);

        $this->assertSame('admin-settings', $resolver->resolve());
    }

    public function testResolveFallsBackToFirstNamespaceWhenNoPrefixMatches(): void
    {
        $registry = new ViewRouteNamespaceRegistry();
        $registry->append(['/admin' => 'admin', '/api' => 'api']);
        $context = new RequestContext();
        $context->set((new ServerRequest())->withUri(new Uri('https://example.com/public/page')));
        $resolver = new ViewRouteNamespaceResolver($registry, $context);

        $this->assertSame('admin', $resolver->resolve());
    }

    public function testResolveReturnsNullForEmptyMap(): void
    {
        $context = new RequestContext();
        $context->set((new ServerRequest())->withUri(new Uri('https://example.com/')));
        $resolver = new ViewRouteNamespaceResolver(new ViewRouteNamespaceRegistry(), $context);

        $this->assertNull($resolver->resolve());
    }

    public function testResolveThrowsWhenRequestContextIsEmpty(): void
    {
        $resolver = new ViewRouteNamespaceResolver(new ViewRouteNamespaceRegistry(), new RequestContext());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP request is not available in RequestContext.');

        $resolver->resolve();
    }
}
