<?php declare(strict_types=1);

namespace Tests\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class TempProject
{
    public readonly string $root;

    public readonly string $configDirectory;

    public function __construct()
    {
        $this->root = sys_get_temp_dir() . '/concept-config-test-' . uniqid('', true);
        $this->configDirectory = $this->root . '/config';
        mkdir($this->configDirectory, 0777, true);
    }

    public function writeConfig(string $relativePath, string $contents): void
    {
        $path = $this->configDirectory . '/' . ltrim($relativePath, '/');
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, $contents);
    }

    public function writeEnv(string $contents): void
    {
        file_put_contents($this->root . '/.env', $contents);
    }

    public function dispose(): void
    {
        if (!is_dir($this->root)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($this->root);
    }
}
