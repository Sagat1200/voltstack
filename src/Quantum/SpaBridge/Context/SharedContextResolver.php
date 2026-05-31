<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Context;

use InvalidArgumentException;
use Quantum\Container\Contracts\ContainerInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextRegistryInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextResolverInterface;

final class SharedContextResolver implements SharedContextResolverInterface
{
    public function __construct(
        protected SharedContextRegistryInterface $registry = new SharedContextRegistry(),
        protected ?ContainerInterface $container = null,
    ) {
    }

    public function resolve(): array
    {
        $context = [];

        foreach ($this->registry->providers() as $provider) {
            $context = array_replace_recursive($context, $this->resolveProvider($provider)->provide());
        }

        return $context;
    }

    protected function resolveProvider(string|SharedContextProviderInterface $provider): SharedContextProviderInterface
    {
        if ($provider instanceof SharedContextProviderInterface) {
            return $provider;
        }

        $resolved = $this->container?->make($provider) ?? new $provider();

        if (!$resolved instanceof SharedContextProviderInterface) {
            throw new InvalidArgumentException(sprintf(
                'Shared context provider [%s] must implement [%s].',
                $provider,
                SharedContextProviderInterface::class,
            ));
        }

        return $resolved;
    }
}
