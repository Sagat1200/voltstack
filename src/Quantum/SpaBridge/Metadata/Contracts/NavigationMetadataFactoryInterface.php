<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Metadata\Contracts;

interface NavigationMetadataFactoryInterface
{
    public function make(array $meta = []): array;
}
