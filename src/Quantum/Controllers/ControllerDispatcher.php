<?php

declare(strict_types=1);

namespace Quantum\Controllers;

use Closure;
use Psr\Container\ContainerInterface;
use Quantum\Http\FormRequest;
use Quantum\Http\Request;
use Quantum\Routing\ResolvedRoute;
use Quantum\Routing\Route;
use Quantum\Routing\RouteBindingRegistry;
use Quantum\Validation\Contracts\ValidatorInterface;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

final class ControllerDispatcher
{
    public function __construct(
        protected ContainerInterface $container,
        protected ?RouteBindingRegistry $bindings = null,
        protected ?ValidatorInterface $validator = null,
    ) {}

    public function dispatchResolvedRoute(ResolvedRoute $resolved, Request $request): mixed
    {
        return $this->dispatchHandler(
            $resolved->route()->handler(),
            $request,
            $resolved->parameters(),
        );
    }

    public function dispatchHandler(mixed $handler, Request $request, array $parameters = []): mixed
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $instance = $this->container->get($class);

            return $this->invokeCallable([$instance, $method], $request, $parameters);
        }

        if (is_string($handler) && class_exists($handler)) {
            $instance = $this->container->get($handler);

            if (!is_callable($instance)) {
                throw new RuntimeException("Handler [$handler] is not invokable.");
            }

            return $this->invokeCallable($instance, $request, $parameters);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$target, $method] = $handler;
            $instance = is_string($target) ? $this->container->get($target) : $target;

            return $this->invokeCallable([$instance, $method], $request, $parameters);
        }

        if (is_callable($handler)) {
            return $this->invokeCallable($handler, $request, $parameters);
        }

        throw new RuntimeException('Route handler could not be dispatched.');
    }

    protected function invokeCallable(callable $callable, Request $request, array $routeParameters): mixed
    {
        $reflection = is_array($callable)
            ? new ReflectionMethod($callable[0], (string) $callable[1])
            : new ReflectionFunction(Closure::fromCallable($callable));

        $arguments = [];

        foreach ($reflection->getParameters() as $parameter) {
            $arguments[] = $this->resolveArgument($parameter, $request, $routeParameters);
        }

        return $callable(...$arguments);
    }

    protected function resolveArgument(
        ReflectionParameter $parameter,
        Request $request,
        array $routeParameters,
    ): mixed {
        $name = $parameter->getName();

        if (array_key_exists($name, $routeParameters)) {
            return $this->routeBindings()->resolve(
                $parameter,
                $routeParameters[$name],
                $request,
                $request->attribute('route')
            );
        }

        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $typeName = $type->getName();

            if ($typeName === Request::class) {
                return $request;
            }

            if ($typeName === Route::class) {
                return $request->attribute('route');
            }

            if (is_a($typeName, FormRequest::class, true)) {
                return $typeName::from($request, $this->validator());
            }

            return $this->container->get($typeName);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new RuntimeException(
            sprintf('Unable to resolve argument [%s] for route handler.', $name)
        );
    }

    protected function routeBindings(): RouteBindingRegistry
    {
        if ($this->bindings instanceof RouteBindingRegistry) {
            return $this->bindings;
        }

        if ($this->container->has(RouteBindingRegistry::class)) {
            /** @var RouteBindingRegistry $bindings */
            $bindings = $this->container->get(RouteBindingRegistry::class);

            return $bindings;
        }

        return new RouteBindingRegistry();
    }

    protected function validator(): ValidatorInterface
    {
        if ($this->validator instanceof ValidatorInterface) {
            return $this->validator;
        }

        if ($this->container->has(ValidatorInterface::class)) {
            /** @var ValidatorInterface $validator */
            $validator = $this->container->get(ValidatorInterface::class);

            return $validator;
        }

        throw new RuntimeException('ValidatorInterface is not available for FormRequest resolution.');
    }
}
