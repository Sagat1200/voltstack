<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Adapters\Contracts;

interface AssetManifestInterface
{
    public function all(): array;

    public function has(string $asset): bool;

    public function get(string $asset): mixed;
}