<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\SpaBridge;

use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use Quantum\SpaBridge\Context\SharedContextRegistry;
use Quantum\SpaBridge\Context\SharedContextResolver;
use VoltStack\Framework\Tests\TestCase;

final class SharedContextTest extends TestCase
{
    public function test_shared_context_resolver_merges_registered_providers(): void
    {
        $registry = new SharedContextRegistry();
        $registry->register(new AuthSharedContextProvider());
        $registry->register(new LocaleSharedContextProvider());

        $resolver = new SharedContextResolver($registry);

        self::assertSame([
            'auth' => [
                'user' => [
                    'id' => 1,
                    'name' => 'Francisco',
                ],
            ],
            'locale' => [
                'current' => 'es_MX',
            ],
        ], $resolver->resolve());
    }

    public function test_spa_bridge_injects_shared_context_into_page_payloads(): void
    {
        $app = $this->createApplication();
        $app->shareSpaContext(AuthSharedContextProvider::class);
        $app->shareSpaContext(LocaleSharedContextProvider::class);

        $payload = $app->spa()->page('Dashboard/Home')->toPayload()->toArray();

        self::assertSame([
            'auth' => [
                'user' => [
                    'id' => 1,
                    'name' => 'Francisco',
                ],
            ],
            'locale' => [
                'current' => 'es_MX',
            ],
        ], $payload['context']);
    }
}

final class AuthSharedContextProvider implements SharedContextProviderInterface
{
    public function provide(): array
    {
        return [
            'auth' => [
                'user' => [
                    'id' => 1,
                    'name' => 'Francisco',
                ],
            ],
        ];
    }
}

final class LocaleSharedContextProvider implements SharedContextProviderInterface
{
    public function provide(): array
    {
        return [
            'locale' => [
                'current' => 'es_MX',
            ],
        ];
    }
}
