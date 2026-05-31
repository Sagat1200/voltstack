<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Metadata;

use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;
use Quantum\SpaBridge\Metadata\Contracts\FrontendMetadataFactoryInterface;

final class FrontendMetadataFactory implements FrontendMetadataFactoryInterface
{
    public function make(array $meta = [], ?FrontendAdapterInterface $adapter = null, ?string $component = null): array
    {
        if (! $adapter instanceof FrontendAdapterInterface) {
            return $meta;
        }

        $frontend = [
            'adapter' => [
                'name' => $adapter->name(),
                'version' => $adapter->version(),
            ],
            'entrypoints' => $adapter->entrypoints(),
        ];

        if (is_string($component) && $component !== '') {
            $resolved = $adapter->resolveComponent($component);

            if ($resolved !== null) {
                $frontend['resolved_component'] = $resolved;
            }
        }

        return array_replace_recursive([
            'frontend' => $frontend,
        ], $meta);
    }
}