<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Pages\Contracts;

interface PageComponentResolverInterface
{
    public function resolve(string $component): string;
}
