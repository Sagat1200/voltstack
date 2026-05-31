<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Context\Contracts;

interface SharedContextResolverInterface
{
    public function resolve(): array;
}
