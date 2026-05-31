<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\SpaBridge;

use Quantum\SpaBridge\Adapters\ArrayAssetManifest;
use Quantum\SpaBridge\Adapters\ArrayComponentResolver;
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
        self::assertSame($adapter, $app->spa()->adapter());
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
}
