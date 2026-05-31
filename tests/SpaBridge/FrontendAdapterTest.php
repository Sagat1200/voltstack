<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\SpaBridge;

use Quantum\SpaBridge\Adapters\ArrayAssetManifest;
use Quantum\SpaBridge\Adapters\ArrayComponentResolver;
use Quantum\SpaBridge\Adapters\Contracts\ComponentResolverInterface;
use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;
use Quantum\SpaBridge\Adapters\NullFrontendAdapter;
use VoltStack\Framework\Tests\TestCase;

final class FrontendAdapterTest extends TestCase
{
    public function test_application_exposes_null_frontend_adapter_by_default(): void
    {
        $app = $this->createApplication();
        $adapter = $app->spaAdapter();

        self::assertInstanceOf(FrontendAdapterInterface::class, $adapter);
        self::assertSame('null', $adapter->name());
        self::assertSame('0.0.0', $adapter->version());
        self::assertSame([], $adapter->entrypoints());
        self::assertNull($adapter->resolveComponent('Dashboard/Home'));
        self::assertSame('null', $app->spa()->adapter()->name());
    }

    public function test_array_manifest_and_component_resolver_support_explicit_and_manifest_entries(): void
    {
        $manifest = new ArrayAssetManifest([
            'Dashboard/Home' => [
                'file' => '/build/dashboard.js',
            ],
        ]);
        $resolver = new ArrayComponentResolver([
            'Users/Index' => [
                'file' => '/build/users.js',
            ],
        ], $manifest);

        self::assertTrue($manifest->has('Dashboard/Home'));
        self::assertSame([
            'file' => '/build/dashboard.js',
        ], $manifest->get('Dashboard/Home'));
        self::assertSame([
            'file' => '/build/users.js',
        ], $resolver->resolve('Users/Index'));
        self::assertSame([
            'file' => '/build/dashboard.js',
        ], $resolver->resolve('Dashboard/Home'));
        self::assertNull($resolver->resolve('Missing/Component'));
    }

    public function test_null_frontend_adapter_delegates_component_resolution(): void
    {
        $adapter = new NullFrontendAdapter(
            new ArrayComponentResolver([
                'Dashboard/Home' => [
                    'file' => '/build/dashboard.js',
                ],
            ]),
            ['/build/app.js'],
            '0.1.0',
        );

        self::assertSame('null', $adapter->name());
        self::assertSame('0.1.0', $adapter->version());
        self::assertSame(['/build/app.js'], $adapter->entrypoints());
        self::assertSame([
            'file' => '/build/dashboard.js',
        ], $adapter->resolveComponent('Dashboard/Home'));
    }

    public function test_application_can_switch_to_a_configured_frontend_adapter_after_boot(): void
    {
        $app = $this->createApplication();
        $app->boot();

        $manifestPath = $this->writeManifest([
            'Dashboard/Home' => [
                'file' => '/build/dashboard.js',
            ],
        ]);

        $app->config()->set('spa.frontend.adapter', [
            'class' => ConfiguredTestFrontendAdapter::class,
            'factory' => 'fromManifest',
            'manifest' => $manifestPath,
            'entrypoints' => ['/build/app.js'],
            'name' => 'configured',
            'version' => '1.2.3',
        ]);

        $adapter = $app->spaAdapter();

        self::assertInstanceOf(ConfiguredTestFrontendAdapter::class, $adapter);
        self::assertSame('configured', $adapter->name());
        self::assertSame('1.2.3', $adapter->version());
        self::assertSame(['/build/app.js'], $adapter->entrypoints());
        self::assertSame('/build/dashboard.js', $adapter->resolveComponent('Dashboard/Home')['file']);
    }

    public function test_application_resolves_relative_manifest_paths_against_the_project_base_path(): void
    {
        $app = $this->createApplication();
        @mkdir($app->basePath('storage/spa'), 0777, true);
        file_put_contents(
            $app->basePath('storage/spa/manifest.json'),
            json_encode([
                'Dashboard/Home' => [
                    'file' => '/build/dashboard.js',
                ],
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
        );

        $app->boot();
        $app->config()->set('spa.frontend.adapter', [
            'class' => ConfiguredTestFrontendAdapter::class,
            'factory' => 'fromManifest',
            'manifest' => 'storage/spa/manifest.json',
            'entrypoints' => ['/build/app.js'],
            'name' => 'configured',
            'version' => '1.2.4',
        ]);

        $adapter = $app->spaAdapter();

        self::assertInstanceOf(ConfiguredTestFrontendAdapter::class, $adapter);
        self::assertSame('/build/dashboard.js', $adapter->resolveComponent('Dashboard/Home')['file']);
        self::assertSame(['/build/app.js'], $adapter->entrypoints());
    }

    public function test_spa_bridge_resolves_the_adapter_lazily_after_boot(): void
    {
        $app = $this->createApplication();
        $bridge = $app->spa();

        self::assertSame('null', $bridge->adapter()->name());

        $app->boot();

        $manifestPath = $this->writeManifest([
            'Reports/Index' => [
                'file' => '/build/reports.js',
            ],
        ]);

        $app->config()->set('spa.frontend.adapter', [
            'class' => ConfiguredTestFrontendAdapter::class,
            'factory' => 'fromManifest',
            'manifest' => $manifestPath,
            'name' => 'configured',
            'version' => '2.0.0',
        ]);

        $adapter = $bridge->adapter();

        self::assertInstanceOf(ConfiguredTestFrontendAdapter::class, $adapter);
        self::assertSame('configured', $adapter->name());
        self::assertSame('2.0.0', $adapter->version());
        self::assertSame('/build/reports.js', $adapter->resolveComponent('Reports/Index')['file']);
    }

    private function writeManifest(array $manifest): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'voltstack-spa-manifest-' . bin2hex(random_bytes(8)) . '.json';
        file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return $path;
    }
}

final class ConfiguredTestFrontendAdapter implements FrontendAdapterInterface
{
    public function __construct(
        private string $name = 'configured',
        private string $version = '0.1.0',
        private array $entrypoints = [],
        private ?ComponentResolverInterface $components = null,
    ) {
    }

    public static function fromManifest(
        string $manifestPath,
        array $components = [],
        array $entrypoints = [],
        string $name = 'configured',
        string $version = '0.1.0',
    ): self {
        $contents = file_get_contents($manifestPath);
        $manifest = $contents === false ? [] : json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        return new self(
            $name,
            $version,
            $entrypoints,
            new ArrayComponentResolver($components, new ArrayAssetManifest(is_array($manifest) ? $manifest : [])),
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function entrypoints(): array
    {
        return $this->entrypoints;
    }

    public function resolveComponent(string $component): array|string|null
    {
        return $this->components?->resolve($component);
    }
}