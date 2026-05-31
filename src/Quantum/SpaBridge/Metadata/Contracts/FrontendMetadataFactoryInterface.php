<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Metadata\Contracts;

use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;

interface FrontendMetadataFactoryInterface
{
    public function make(array $meta = [], ?FrontendAdapterInterface $adapter = null, ?string $component = null): array;
}
