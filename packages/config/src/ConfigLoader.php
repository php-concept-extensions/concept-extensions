<?php declare(strict_types=1);

namespace Concept\Extensions\Config;

use Concept\Extensions\Config\Contracts\ConfigInterface;
use Dotenv\Dotenv;
use Noodlehaus\Config as NhConfig;

final class ConfigLoader
{
    private const string APP_ENV_KEY = 'APP_ENV';

    public function __construct(
        private readonly string $root,
        private readonly string $configDirectory,
    ) {}

    public function load(): ConfigInterface
    {
        $nhConfig = new NhConfig($this->configDirectory);
        $envData = $this->loadDotEnv($this->root);
        $this->loadOverrideConfig($nhConfig, $envData);
        $this->mergeEnvData($nhConfig, $envData);

        return new Config($nhConfig);
    }

    /**
     * @return array<string, string|null>
     */
    private function loadDotEnv(string $rootPath): array
    {
        $envFile = $rootPath . '/.env';
        if (!is_file($envFile)) {
            return [];
        }

        return Dotenv::createImmutable($rootPath)->load();
    }

    /**
     * @param array<string, string|null> $envData
     */
    private function loadOverrideConfig(NhConfig $nhConfig, array $envData): void
    {
        $env = $envData[self::APP_ENV_KEY] ?? '';
        $overrideConfigPath = rtrim($this->configDirectory, '/') . '/' . $env;
        if (is_dir($overrideConfigPath)) {
            $overrideConfig = new NhConfig($overrideConfigPath);
            $nhConfig->merge($overrideConfig);
        }
    }

    /**
     * @param array<string, mixed> $envData
     */
    private function mergeEnvData(NhConfig $nhConfig, array $envData): void
    {
        foreach ($envData as $key => $value) {
            $parts = explode('_', strtolower($key), 2);
            $root = $parts[0];
            $sub = $parts[1] ?? '';

            $configKey = empty($sub) ? $root : sprintf('%s.%s', $root, $sub);
            $nhConfig->set($configKey, $value);
        }
    }
}
