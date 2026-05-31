<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Adapters\Contracts;

interface ComponentResolverInterface
{
    public function resolve(string $component): array|string|null;
}
