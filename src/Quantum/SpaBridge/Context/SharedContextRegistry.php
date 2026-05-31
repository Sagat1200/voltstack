<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Context;

use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextRegistryInterface;

final class SharedContextRegistry implements SharedContextRegistryInterface
{
    /** @var array<int, string|SharedContextProviderInterface> */
    protected array $providers = [];

    public function register(string|SharedContextProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    public function providers(): array
    {
        return $this->providers;
    }
}
