<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Adapters;

use Quantum\SpaBridge\Adapters\Contracts\AssetManifestInterface;

final class ArrayAssetManifest implements AssetManifestInterface
{
    public function __construct(
        protected array $assets = [],
    ) {
    }

    public function all(): array
    {
        return $this->assets;
    }

    public function has(string $asset): bool
    {
        return array_key_exists($asset, $this->assets);
    }

    public function get(string $asset): mixed
    {
        return $this->assets[$asset] ?? null;
    }
}
