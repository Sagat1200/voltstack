<?php

declare(strict_types=1);

namespace Quantum\Routing;

final class RouteGroupRegistrar
{
    /** @var array{prefix?: string, name?: string, middleware?: array<int, mixed>} */
    protected array $attributes = [];

    public function __construct(
        protected Router $router,
        array $attributes = [],
    ) {
        $this->attributes = $attributes;
    }

    public function prefix(string $prefix): self
    {
        $clone = clone $this;
        $clone->attributes['prefix'] = $clone->joinPrefix($clone->attributes['prefix'] ?? '', $prefix);

        return $clone;
    }

    public function middleware(mixed $middleware): self
    {
        $clone = clone $this;
        $current = $clone->attributes['middleware'] ?? [];

        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        $clone->attributes['middleware'] = [...$current, ...$middleware];

        return $clone;
    }

    public function name(string $name): self
    {
        $clone = clone $this;
        $current = $clone->attributes['name'] ?? '';
        $segments = array_filter([
            trim($current, '.'),
            trim($name, '.'),
        ], static fn(string $segment): bool => $segment !== '');

        $clone->attributes['name'] = implode('.', $segments);

        return $clone;
    }

    public function group(callable $callback): void
    {
        $this->router->group($this->attributes, $callback);
    }

    protected function joinPrefix(string $base, string $prefix): string
    {
        $segments = array_filter([
            trim($base, '/'),
            trim($prefix, '/'),
        ], static fn(string $segment): bool => $segment !== '');

        return $segments === [] ? '/' : '/' . implode('/', $segments);
    }
}
