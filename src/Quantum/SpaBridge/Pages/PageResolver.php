<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Pages;

use Quantum\SpaBridge\Metadata\Contracts\NavigationMetadataFactoryInterface;
use Quantum\SpaBridge\Metadata\NavigationMetadataFactory;
use Quantum\SpaBridge\Pages\Contracts\PageComponentResolverInterface;
use Quantum\SpaBridge\Pages\Contracts\PageResolverInterface;

final class PageResolver implements PageResolverInterface
{
    public function __construct(
        protected PageComponentResolverInterface $components = new PageComponentResolver(),
        protected NavigationMetadataFactoryInterface $metadata = new NavigationMetadataFactory(),
    ) {}

    public function resolve(string $component, array $props = [], array $meta = [], array $context = []): PageDefinition
    {
        return new PageDefinition(
            $this->components->resolve($component),
            $props,
            $this->metadata->make($meta),
            $context,
        );
    }
}
