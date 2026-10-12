<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\View\Events\TemplateRendered;
use Concept\Extensions\ViewTwig\TwigView;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class TwigViewTest extends TestCase
{
    public function testRenderAppendsDefaultExtension(): void
    {
        $twig = new Environment(new ArrayLoader(['home.html.twig' => 'Hello {{ name }}']));
        $view = new TwigView($twig, '.html.twig');

        $this->assertSame('Hello Alice', $view->render('home', ['name' => 'Alice']));
    }

    public function testRenderDispatchesTemplateRenderedEvent(): void
    {
        $events = [];
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnCallback(
            function(object $event) use (&$events): object {
                $events[] = $event;

                return $event;
            },
        );

        $twig = new Environment(new ArrayLoader(['page.html.twig' => 'OK']));
        $view = new TwigView($twig, '.html.twig', $dispatcher);

        $view->render('page');

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TemplateRendered::class, $events[0]);
        $this->assertSame('page', $events[0]->view);
    }

    public function testRenderStringUsesInlineTemplate(): void
    {
        $twig = new Environment(new ArrayLoader());
        $view = new TwigView($twig, '.twig');

        $this->assertSame('value', $view->renderString('{{ value }}', ['value' => 'value']));
    }
}
