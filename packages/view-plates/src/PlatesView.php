<?php declare(strict_types=1);

namespace Concept\Extensions\ViewPlates;

use Concept\Extensions\View\Contracts\ViewInterface;
use League\Plates\Engine;

final class PlatesView implements ViewInterface
{
    public function __construct(private readonly Engine $engine) {}

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $viewName, array $data = []): string
    {
        return $this->engine->render($viewName, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderString(string $template, array $data = []): string
    {
        $name = '__string_' . bin2hex(random_bytes(16));
        $path = rtrim($this->engine->getDirectory(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $name
            . '.'
            . $this->engine->getFileExtension();

        try {
            file_put_contents($path, $template);

            return $this->engine->render($name, $data);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
