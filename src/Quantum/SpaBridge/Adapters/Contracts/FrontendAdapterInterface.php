<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Adapters\Contracts;

interface FrontendAdapterInterface
{
    public function name(): string;

    public function version(): string;

    public function entrypoints(): array;

    public function resolveComponent(string $component): array|string|null;
}
