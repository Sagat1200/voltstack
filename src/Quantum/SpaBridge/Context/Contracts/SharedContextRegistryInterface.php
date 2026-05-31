<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Context\Contracts;

interface SharedContextRegistryInterface
{
    public function register(string|SharedContextProviderInterface $provider): void;

    public function providers(): array;
}
