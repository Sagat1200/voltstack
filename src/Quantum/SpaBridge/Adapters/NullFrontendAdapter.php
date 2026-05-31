<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Adapters;

use Quantum\SpaBridge\Adapters\Contracts\ComponentResolverInterface;
use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;

final class NullFrontendAdapter implements FrontendAdapterInterface
{
    public function __construct(
        protected ComponentResolverInterface $components = new ArrayComponentResolver(),
        protected array $entrypoints = [],
        protected string $version = '0.0.0',
    ) {
    }

    public function name(): string
    {
        return 'null';
    }

    public function version(): string
    {
        return $this->version;
    }

    public function entrypoints(): array
    {
        return $this->entrypoints;
    }

    public function resolveComponent(string $component): array|string|null
    {
        return $this->components->resolve($component);
    }
}
