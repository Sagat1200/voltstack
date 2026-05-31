<?php

declare(strict_types=1);

namespace Quantum\HttpKernel;

use Closure;
use Psr\Container\ContainerInterface;
use Quantum\Controllers\ControllerDispatcher;
use Quantum\Exceptions\Contracts\ExceptionHandlerInterface;
use Quantum\Exceptions\NotFoundHttpException;
use Quantum\Http\Request;
use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;
use Quantum\HttpKernel\Contracts\HttpKernelInterface;
use Quantum\HttpKernel\Contracts\MiddlewareInterface;
use Quantum\Routing\ResolvedRoute;
use Quantum\Routing\Router;
use RuntimeException;

final class HttpKernel implements HttpKernelInterface
{
    /** @var array<int, mixed> */
    protected array $middleware = [];

    public function __construct(
        protected Router $router,
        protected ContainerInterface $container,
        protected ResponseFactory $responses = new ResponseFactory(),
        protected ?ExceptionHandlerInterface $exceptions = null,
        protected ?MiddlewareRegistry $middlewareRegistry = null,
        protected ?ControllerDispatcher $dispatcher = null,
    ) {}

    public function handle(Request $request): Response
    {
        try {
            $resolved = $this->router->resolve($request);

            if ($resolved === null) {
                throw new NotFoundHttpException();
            }

            $request = $request
                ->withAttribute('route', $resolved->route())
                ->withAttribute('route_parameters', $resolved->parameters());

            foreach ($resolved->parameters() as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }

            $destination = fn(Request $currentRequest): Response => $this->dispatchResolvedRoute($currentRequest, $resolved);
            $stack = $this->middlewareRegistry()->resolve(
                array_merge($this->middleware, $resolved->route()->middlewares())
            );

            foreach (array_reverse($stack) as $middleware) {
                $next = $destination;
                $destination = fn(Request $currentRequest): Response => $this->runMiddleware(
                    $middleware,
                    $currentRequest,
                    $next,
                );
            }

            return $destination($request);
        } catch (\Throwable $throwable) {
            return $this->exceptionHandler()->render($request, $throwable);
        }
    }

    public function pushMiddleware(mixed $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function aliasMiddleware(string $name, mixed $middleware): self
    {
        $this->middlewareRegistry()->alias($name, $middleware);

        return $this;
    }

    public function middlewareGroup(string $name, array $middleware): self
    {
        $this->middlewareRegistry()->group($name, $middleware);

        return $this;
    }

    protected function runMiddleware(mixed $middleware, Request $request, Closure $next): Response
    {
        $this->bindCurrentRequest($request);

        if (is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->handle($request, $next);
        }

        if (is_callable($middleware)) {
            $response = $middleware($request, $next);

            return $response instanceof Response ? $response : $this->responses->from($response);
        }

        throw new RuntimeException('Invalid middleware provided to HttpKernel.');
    }

    protected function dispatchResolvedRoute(Request $request, ResolvedRoute $resolved): Response
    {
        $this->bindCurrentRequest($request);
        $result = $this->controllerDispatcher()->dispatchResolvedRoute($resolved, $request);

        return $this->responses->from($result);
    }

    protected function bindCurrentRequest(Request $request): void
    {
        if (!method_exists($this->container, 'instance')) {
            return;
        }

        $this->container->instance(Request::class, $request);
        $this->container->instance('request', $request);
    }

    protected function exceptionHandler(): ExceptionHandlerInterface
    {
        if ($this->exceptions instanceof ExceptionHandlerInterface) {
            return $this->exceptions;
        }

        if ($this->container->has(ExceptionHandlerInterface::class)) {
            /** @var ExceptionHandlerInterface $handler */
            $handler = $this->container->get(ExceptionHandlerInterface::class);

            return $handler;
        }

        return new \Quantum\Exceptions\ExceptionHandler($this->responses);
    }

    protected function middlewareRegistry(): MiddlewareRegistry
    {
        if ($this->middlewareRegistry instanceof MiddlewareRegistry) {
            return $this->middlewareRegistry;
        }

        if ($this->container->has(MiddlewareRegistry::class)) {
            /** @var MiddlewareRegistry $registry */
            $registry = $this->container->get(MiddlewareRegistry::class);

            return $registry;
        }

        return new MiddlewareRegistry();
    }

    protected function controllerDispatcher(): ControllerDispatcher
    {
        if ($this->dispatcher instanceof ControllerDispatcher) {
            return $this->dispatcher;
        }

        if ($this->container->has(ControllerDispatcher::class)) {
            /** @var ControllerDispatcher $dispatcher */
            $dispatcher = $this->container->get(ControllerDispatcher::class);

            return $dispatcher;
        }

        return new ControllerDispatcher($this->container);
    }
}
