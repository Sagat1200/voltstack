<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Pages;

use Quantum\SpaBridge\Pages\Contracts\PageComponentResolverInterface;
use Quantum\SpaBridge\Support\ComponentName;

final class PageComponentResolver implements PageComponentResolverInterface
{
    public function resolve(string $component): string
    {
        return ComponentName::normalize($component);
    }
}
