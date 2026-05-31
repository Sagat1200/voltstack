<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Metadata;

use Quantum\Http\Request;
use Quantum\Routing\Route;
use Quantum\SpaBridge\Metadata\Contracts\NavigationMetadataFactoryInterface;
use Throwable;

final class NavigationMetadataFactory implements NavigationMetadataFactoryInterface
{
    public function make(array $meta = []): array
    {
        $request = $this->currentRequest();

        if (!$request instanceof Request) {
            return $meta;
        }

        $navigation = array_filter([
            'url' => $request->uri(),
            'path' => $request->path(),
            'route' => $this->routeName($request),
        ], static fn(mixed $value): bool => $value !== null && $value !== '');

        if ($navigation === []) {
            return $meta;
        }

        return array_replace_recursive([
            'navigation' => $navigation,
        ], $meta);
    }

    protected function currentRequest(): ?Request
    {
        if (!function_exists('app')) {
            return null;
        }

        try {
            $request = app(Request::class);

            return $request instanceof Request ? $request : null;
        } catch (Throwable) {
            return null;
        }
    }

    protected function routeName(Request $request): ?string
    {
        $route = $request->attribute('route');

        if (!$route instanceof Route) {
            return null;
        }

        $name = $route->name();

        return is_string($name) ? $name : null;
    }
}
