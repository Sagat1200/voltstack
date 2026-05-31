<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Adapters;

use Quantum\Container\Container;
use Quantum\SpaBridge\Adapters\Contracts\AssetManifestInterface;
use Quantum\SpaBridge\Adapters\Contracts\ComponentResolverInterface;
use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;
use Throwable;
use VoltStack\Platform\Application;

final class FrontendAdapterFactory
{
    public function __construct(
        protected Application $app,
        protected Container $container,
    ) {}

    public function make(): FrontendAdapterInterface
    {
        $configuration = $this->configuration();

        if ($configuration === []) {
            return $this->fallback();
        }

        return $this->resolveConfiguredAdapter($configuration) ?? $this->fallback($configuration);
    }

    protected function configuration(): array
    {
        if (! $this->app->isBooted()) {
            return [];
        }

        $configuration = $this->app->config()->get('spa.frontend.adapter', []);

        return is_array($configuration) ? $configuration : [];
    }

    protected function resolveConfiguredAdapter(array $configuration): ?FrontendAdapterInterface
    {
        $class = $configuration['class'] ?? null;

        if (! is_string($class) || $class === '' || ! class_exists($class) || ! is_a($class, FrontendAdapterInterface::class, true)) {
            return null;
        }

        $factory = $configuration['factory'] ?? null;

        if (is_string($factory) && method_exists($class, $factory)) {
            try {
                $adapter = $class::{$factory}(
                    manifestPath: $this->manifestPath($configuration),
                    components: $this->componentsMap($configuration),
                    entrypoints: $this->entrypoints($configuration),
                    name: $this->name($configuration),
                    version: $this->version($configuration),
                );

                if ($adapter instanceof FrontendAdapterInterface) {
                    return $adapter;
                }
            } catch (Throwable) {
                return null;
            }
        }

        try {
            $adapter = $this->container->make($class, [
                'name' => $this->name($configuration),
                'version' => $this->version($configuration),
                'entrypoints' => $this->entrypoints($configuration),
                'components' => $this->componentResolver($configuration),
            ]);

            return $adapter instanceof FrontendAdapterInterface ? $adapter : null;
        } catch (Throwable) {
            return null;
        }
    }

    protected function fallback(array $configuration = []): FrontendAdapterInterface
    {
        return new NullFrontendAdapter(
            $this->componentResolver($configuration),
            $this->entrypoints($configuration),
            $this->version($configuration, '0.0.0'),
        );
    }

    protected function componentResolver(array $configuration): ComponentResolverInterface
    {
        return new ArrayComponentResolver(
            $this->componentsMap($configuration),
            $this->manifest($configuration),
        );
    }

    protected function manifest(array $configuration): AssetManifestInterface
    {
        $manifest = $configuration['manifest'] ?? [];

        if (is_array($manifest)) {
            return new ArrayAssetManifest($manifest);
        }

        if (is_string($manifest) && $manifest !== '') {
            $path = $this->manifestPath($configuration);
            $contents = is_file($path) ? file_get_contents($path) : false;

            if ($contents !== false) {
                $decoded = json_decode($contents, true);

                if (is_array($decoded)) {
                    return new ArrayAssetManifest($decoded);
                }
            }
        }

        return new ArrayAssetManifest();
    }

    protected function manifestPath(array $configuration): string
    {
        $manifest = $configuration['manifest'] ?? '';

        return is_string($manifest) ? $this->resolvePath($manifest) : '';
    }

    protected function resolvePath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return $this->app->basePath($path);
    }

    protected function componentsMap(array $configuration): array
    {
        return is_array($configuration['components'] ?? null) ? $configuration['components'] : [];
    }

    protected function entrypoints(array $configuration): array
    {
        return is_array($configuration['entrypoints'] ?? null) ? array_values($configuration['entrypoints']) : [];
    }

    protected function name(array $configuration, string $default = 'configurable'): string
    {
        return is_string($configuration['name'] ?? null) && $configuration['name'] !== ''
            ? $configuration['name']
            : $default;
    }

    protected function version(array $configuration, string $default = '0.1.0'): string
    {
        return is_string($configuration['version'] ?? null) && $configuration['version'] !== ''
            ? $configuration['version']
            : $default;
    }
}
