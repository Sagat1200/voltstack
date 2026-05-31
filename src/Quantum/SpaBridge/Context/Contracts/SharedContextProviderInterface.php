<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Context\Contracts;

interface SharedContextProviderInterface
{
    public function provide(): array;
}