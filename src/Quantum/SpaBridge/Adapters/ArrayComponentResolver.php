<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Adapters;

use Quantum\SpaBridge\Adapters\Contracts\AssetManifestInterface;
use Quantum\SpaBridge\Adapters\Contracts\ComponentResolverInterface;

final class ArrayComponentResolver implements ComponentResolverInterface
{
    public function __construct(
        protected array $map = [],
        protected AssetManifestInterface $manifest = new ArrayAssetManifest(),
    ) {
    }

    public function resolve(string $component): array|string|null
    {
        if (array_key_exists($component, $this->map)) {
            return $this->map[$component];
        }

        return $this->manifest->get($component);
    }
}
