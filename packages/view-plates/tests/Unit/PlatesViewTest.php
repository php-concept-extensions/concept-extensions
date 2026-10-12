<?php declare(strict_types=1);

namespace Tests\Unit;

use Concept\Extensions\ViewPlates\PlatesView;
use League\Plates\Engine;
use PHPUnit\Framework\TestCase;

final class PlatesViewTest extends TestCase
{
    private string $viewsPath;

    protected function setUp(): void
    {
        $this->viewsPath = sys_get_temp_dir() . '/plates-view-' . uniqid('', true);
        mkdir($this->viewsPath);
        file_put_contents($this->viewsPath . '/hello.php', 'Hello <?= $this->e($name) ?>');
    }

    protected function tearDown(): void
    {
        if (is_file($this->viewsPath . '/hello.php')) {
            unlink($this->viewsPath . '/hello.php');
        }
        if (is_dir($this->viewsPath)) {
            rmdir($this->viewsPath);
        }
    }

    public function testRenderDelegatesToEngine(): void
    {
        $view = new PlatesView(new Engine($this->viewsPath));

        $this->assertSame('Hello Alice', $view->render('hello', ['name' => 'Alice']));
    }

    public function testRenderStringWritesTemporaryTemplate(): void
    {
        $view = new PlatesView(new Engine($this->viewsPath));

        $this->assertSame('Hi Bob', $view->renderString('Hi <?= $this->e($name) ?>', ['name' => 'Bob']));
        $this->assertSame([], glob($this->viewsPath . '/__string_*'));
    }
}
