<?php

declare(strict_types=1);

namespace Quantum\Routing;

use Quantum\Http\Request;

final class Router
{
    /** @var array<int, Route> */
    protected array $routes = [];

    /** @var array<int, array{prefix?: string, name?: string, middleware?: array<int, mixed>}> */
    protected array $groupStack = [];

    public function add(array $methods, string $uri, mixed $handler): Route
    {
        $route = new Route($methods, $this->applyGroupPrefix($uri), $handler, $this->currentGroupName());
        $groupMiddleware = $this->currentGroupMiddleware();

        if ($groupMiddleware !== []) {
            $route->middleware($groupMiddleware);
        }

        $this->routes[] = $route;

        return $route;
    }

    public function match(array $methods, string $uri, mixed $handler): Route
    {
        return $this->add($methods, $uri, $handler);
    }

    public function any(string $uri, mixed $handler): Route
    {
        return $this->add(['ANY'], $uri, $handler);
    }

    public function get(string $uri, mixed $handler): Route
    {
        return $this->add(['GET'], $uri, $handler);
    }

    public function post(string $uri, mixed $handler): Route
    {
        return $this->add(['POST'], $uri, $handler);
    }

    public function put(string $uri, mixed $handler): Route
    {
        return $this->add(['PUT'], $uri, $handler);
    }

    public function patch(string $uri, mixed $handler): Route
    {
        return $this->add(['PATCH'], $uri, $handler);
    }

    public function delete(string $uri, mixed $handler): Route
    {
        return $this->add(['DELETE'], $uri, $handler);
    }

    /**
     * @param array{prefix?: string, name?: string, middleware?: array<int, mixed>|mixed} $attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $this->normalizeGroupAttributes($attributes);

        try {
            $callback($this);
        } finally {
            array_pop($this->groupStack);
        }
    }

    public function prefix(string $prefix): RouteGroupRegistrar
    {
        return (new RouteGroupRegistrar($this))->prefix($prefix);
    }

    public function middleware(mixed $middleware): RouteGroupRegistrar
    {
        return (new RouteGroupRegistrar($this))->middleware($middleware);
    }

    public function name(string $name): RouteGroupRegistrar
    {
        return (new RouteGroupRegistrar($this))->name($name);
    }

    public function resolve(Request $request): ?ResolvedRoute
    {
        foreach ($this->routes as $route) {
            if (!$route->allowsMethod($request->method())) {
                continue;
            }

            $parameters = $route->matchesPath($request->path());

            if ($parameters !== null) {
                return new ResolvedRoute($route, $parameters);
            }
        }

        return null;
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function route(string $name, array $parameters = [], array $query = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->name() === $name) {
                return $route->url($parameters, $query);
            }
        }

        throw new \RuntimeException(
            sprintf('Route [%s] is not defined.', $name)
        );
    }

    protected function applyGroupPrefix(string $uri): string
    {
        $prefix = '';

        foreach ($this->groupStack as $group) {
            $prefix = $this->joinSegments($prefix, $group['prefix'] ?? '');
        }

        return $this->joinSegments($prefix, $uri);
    }

    protected function currentGroupMiddleware(): array
    {
        $middleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middleware = [...$middleware, ...$group['middleware']];
            }
        }

        return $middleware;
    }

    protected function currentGroupName(): string
    {
        $segments = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['name'])) {
                $segments[] = trim($group['name'], '.');
            }
        }

        $segments = array_values(array_filter($segments, static fn(string $segment): bool => $segment !== ''));

        return implode('.', $segments);
    }

    /**
     * @param array{prefix?: string, name?: string, middleware?: array<int, mixed>|mixed} $attributes
     * @return array{prefix?: string, name?: string, middleware?: array<int, mixed>}
     */
    protected function normalizeGroupAttributes(array $attributes): array
    {
        $normalized = [];

        if (isset($attributes['prefix'])) {
            $normalized['prefix'] = (string) $attributes['prefix'];
        }

        if (isset($attributes['name'])) {
            $normalized['name'] = (string) $attributes['name'];
        } elseif (isset($attributes['as'])) {
            $normalized['name'] = (string) $attributes['as'];
        }

        if (array_key_exists('middleware', $attributes)) {
            $middleware = $attributes['middleware'];
            $normalized['middleware'] = is_array($middleware) ? $middleware : [$middleware];
        }

        return $normalized;
    }

    protected function joinSegments(string $base, string $append): string
    {
        $segments = array_filter([
            trim($base, '/'),
            trim($append, '/'),
        ], static fn(string $segment): bool => $segment !== '');

        return $segments === [] ? '/' : '/' . implode('/', $segments);
    }
}
