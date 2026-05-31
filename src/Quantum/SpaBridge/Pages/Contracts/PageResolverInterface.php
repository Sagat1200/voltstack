<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Pages\Contracts;

use Quantum\SpaBridge\Pages\PageDefinition;

interface PageResolverInterface
{
    public function resolve(string $component, array $props = [], array $meta = [], array $context = []): PageDefinition;
}