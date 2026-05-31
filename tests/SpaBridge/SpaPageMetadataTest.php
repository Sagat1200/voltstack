<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\SpaBridge;

use InvalidArgumentException;
use Quantum\Http\Request;
use Quantum\SpaBridge\Adapters\ArrayAssetManifest;
use Quantum\SpaBridge\Adapters\ArrayComponentResolver;
use Quantum\SpaBridge\Adapters\Contracts\ComponentResolverInterface;
use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;
use VoltStack\Framework\Tests\TestCase;

final class SpaPageMetadataTest extends TestCase
{
    public function test_spa_page_includes_navigation_metadata_for_named_routes(): void
    {
        $app = $this->createApplication();
        $app->router()
            ->get('/dashboard', fn() => $app->spa()->page('Dashboard\\Home', [], [
                'title' => 'Dashboard',
            ]))
            ->name('dashboard.home');

        $response = $app->kernel()->handle(Request::create('GET', '/dashboard'));
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Dashboard/Home', $data['component']);
        self::assertSame('Dashboard', $data['meta']['title']);
        self::assertSame('/dashboard', $data['meta']['navigation']['url']);
        self::assertSame('/dashboard', $data['meta']['navigation']['path']);
        self::assertSame('dashboard.home', $data['meta']['navigation']['route']);
        self::assertSame('null', $data['meta']['frontend']['adapter']['name']);
        self::assertSame('0.0.0', $data['meta']['frontend']['adapter']['version']);
        self::assertSame([], $data['meta']['frontend']['entrypoints']);
    }

    public function test_spa_page_includes_configured_adapter_metadata_and_resolved_component(): void
    {
        $app = $this->createApplication();
        $app->boot();

        $manifestPath = $this->writeManifest([
            'Dashboard/Home' => [
                'file' => '/build/dashboard.js',
                'css' => ['/build/dashboard.css'],
            ],
        ]);

        $app->config()->set('spa.frontend.adapter', [
            'class' => ConfiguredMetadataFrontendAdapter::class,
            'factory' => 'fromManifest',
            'manifest' => $manifestPath,
            'entrypoints' => ['/build/app.js'],
            'name' => 'configurable',
            'version' => '3.0.0',
        ]);

        $app->router()
            ->get('/spa/metadata', fn() => $app->spa()->page('Dashboard/Home'))
            ->name('spa.metadata');

        $response = $app->kernel()->handle(Request::create('GET', '/spa/metadata'));
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('configurable', $data['meta']['frontend']['adapter']['name']);
        self::assertSame('3.0.0', $data['meta']['frontend']['adapter']['version']);
        self::assertSame(['/build/app.js'], $data['meta']['frontend']['entrypoints']);
        self::assertSame([
            'file' => '/build/dashboard.js',
            'css' => ['/build/dashboard.css'],
        ], $data['meta']['frontend']['resolved_component']);
    }

    public function test_spa_page_rejects_invalid_component_names(): void
    {
        $app = $this->createApplication();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SPA component name');

        $app->spa()->page('Dashboard Home');
    }

    private function writeManifest(array $manifest): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'voltstack-spa-metadata-' . bin2hex(random_bytes(8)) . '.json';
        file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return $path;
    }
}

final class ConfiguredMetadataFrontendAdapter implements FrontendAdapterInterface
{
    public function __construct(
        private string $name = 'configurable',
        private string $version = '0.1.0',
        private array $entrypoints = [],
        private ?ComponentResolverInterface $components = null,
    ) {
    }

    public static function fromManifest(
        string $manifestPath,
        array $components = [],
        array $entrypoints = [],
        string $name = 'configurable',
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
