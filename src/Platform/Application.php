<?php

declare(strict_types=1);

namespace VoltStack\Platform;

use Quantum\Actions\ActionDispatcher;
use Quantum\Bootstrap\BootstrapManager;
use Quantum\Bootstrap\ProviderRepository;
use Quantum\Config\ConfigRepository;
use Quantum\Container\Container;
use Quantum\Container\Contracts\ContainerInterface;
use Quantum\Controllers\ControllerDispatcher;
use Quantum\Exceptions\Contracts\ExceptionHandlerInterface;
use Quantum\Exceptions\ExceptionHandler;
use Quantum\Http\ResponseFactory;
use Quantum\HttpKernel\HttpKernel;
use Quantum\HttpKernel\MiddlewareRegistry;
use Quantum\Routing\RouteBindingRegistry;
use Quantum\Routing\Router;
use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextRegistryInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextResolverInterface;
use Quantum\SpaBridge\Context\SharedContextRegistry;
use Quantum\SpaBridge\Context\SharedContextResolver;
use Quantum\SpaBridge\Contracts\SpaBridgeInterface;
use Quantum\SpaBridge\Contracts\SpaResponderInterface;
use Quantum\SpaBridge\Http\Middleware\HandleSpaRequests;
use Quantum\SpaBridge\Http\SpaResponseFactory;
use Quantum\SpaBridge\Http\SpaResponseNormalizer;
use Quantum\SpaBridge\Metadata\Contracts\NavigationMetadataFactoryInterface;
use Quantum\SpaBridge\Metadata\NavigationMetadataFactory;
use Quantum\SpaBridge\Pages\Contracts\PageComponentResolverInterface;
use Quantum\SpaBridge\Pages\Contracts\PageResolverInterface;
use Quantum\SpaBridge\Pages\PageComponentResolver;
use Quantum\SpaBridge\Pages\PageResolver;
use Quantum\SpaBridge\SpaBridge;
use Quantum\SpaBridge\SpaResponder;
use Quantum\Validation\Contracts\ValidatorInterface;
use Quantum\Validation\Validator;

final class Application
{
    protected string $basePath;

    protected bool $booted = false;

    protected Container $container;

    protected ProviderRepository $providers;

    public function __construct(string $basePath)
    {
        $this->setBasePath($basePath);
        $this->container = new Container();
        $this->providers = new ProviderRepository($this);

        $this->container->instance(self::class, $this);
        $this->container->instance('app', $this);
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(ContainerInterface::class, $this->container);
        $this->container->instance('container', $this->container);
        $this->container->instance(ProviderRepository::class, $this->providers);
        $this->container->instance('providers', $this->providers);
        $this->container->singleton(SpaResponseFactory::class, SpaResponseFactory::class);
        $this->container->singleton(SpaResponseNormalizer::class, static fn(Container $container, array $parameters = []): SpaResponseNormalizer => new SpaResponseNormalizer(
            $container->make(SpaResponseFactory::class),
        ));
        $this->container->singleton(ResponseFactory::class, static fn(Container $container, array $parameters = []): ResponseFactory => new ResponseFactory(
            static fn(mixed $result): ?\Quantum\Http\Response => $container->make(SpaResponseNormalizer::class)->normalize($result),
        ));
        $this->container->singleton(SharedContextRegistry::class, SharedContextRegistry::class);
        $this->container->singleton(SharedContextRegistryInterface::class, SharedContextRegistry::class);
        $this->container->singleton(SharedContextResolver::class, static fn(Container $container, array $parameters = []): SharedContextResolver => new SharedContextResolver(
            $container->make(SharedContextRegistryInterface::class),
            $container,
        ));
        $this->container->singleton(SharedContextResolverInterface::class, SharedContextResolver::class);
        $this->container->singleton(PageComponentResolver::class, PageComponentResolver::class);
        $this->container->singleton(PageComponentResolverInterface::class, PageComponentResolver::class);
        $this->container->singleton(NavigationMetadataFactory::class, NavigationMetadataFactory::class);
        $this->container->singleton(NavigationMetadataFactoryInterface::class, NavigationMetadataFactory::class);
        $this->container->singleton(PageResolver::class, static fn(Container $container, array $parameters = []): PageResolver => new PageResolver(
            $container->make(PageComponentResolverInterface::class),
            $container->make(NavigationMetadataFactoryInterface::class),
        ));
        $this->container->singleton(PageResolverInterface::class, PageResolver::class);
        $this->container->singleton(SpaResponder::class, static fn(Container $container, array $parameters = []): SpaResponder => new SpaResponder(
            $container->make(ResponseFactory::class),
            $container->make(SharedContextResolverInterface::class),
            $container->make(PageResolverInterface::class),
        ));
        $this->container->singleton(SpaResponderInterface::class, SpaResponder::class);
        $this->container->singleton(SpaBridge::class, static fn(Container $container, array $parameters = []): SpaBridge => new SpaBridge(
            $container->make(SpaResponderInterface::class),
            $container->make(SharedContextRegistryInterface::class),
            $container->make(SharedContextResolverInterface::class),
            $container->make(PageResolverInterface::class),
        ));
        $this->container->singleton(SpaBridgeInterface::class, SpaBridge::class);
        $this->container->singleton(MiddlewareRegistry::class, MiddlewareRegistry::class);
        $this->container->singleton(HandleSpaRequests::class, HandleSpaRequests::class);
        $this->container->singleton(RouteBindingRegistry::class, RouteBindingRegistry::class);
        $this->container->singleton(Validator::class, Validator::class);
        $this->container->singleton(ValidatorInterface::class, Validator::class);
        $this->container->singleton(
            ControllerDispatcher::class,
            static fn(Container $container, array $parameters = []): ControllerDispatcher => new ControllerDispatcher(
                $container,
                $container->make(RouteBindingRegistry::class),
                $container->make(ValidatorInterface::class),
            )
        );
        $this->container->singleton(ActionDispatcher::class, static fn(Container $container, array $parameters = []): ActionDispatcher => new ActionDispatcher(
            $container,
            $container->make(ValidatorInterface::class),
        ));
        $this->container->singleton(ExceptionHandler::class, static fn(Container $container, array $parameters = []): ExceptionHandler => new ExceptionHandler(
            $container->make(ResponseFactory::class),
            $container->make(SpaResponseFactory::class),
            $container->make(SharedContextResolverInterface::class),
        ));
        $this->container->singleton(ExceptionHandlerInterface::class, ExceptionHandler::class);
        $this->container->singleton(
            Router::class,
            static fn(Container $container, array $parameters = []): Router => new Router()
        );
        $this->container->singleton(
            HttpKernel::class,
            static fn(Container $container, array $parameters = []): HttpKernel => new HttpKernel(
                $container->make(Router::class),
                $container,
                $container->make(ResponseFactory::class),
                $container->make(ExceptionHandlerInterface::class),
                $container->make(MiddlewareRegistry::class),
                $container->make(ControllerDispatcher::class),
            )
        );
        $this->container->instance('router', $this->container->make(Router::class));
        $this->container->instance('kernel', $this->container->make(HttpKernel::class));
        $this->container->instance('spa', $this->container->make(SpaBridgeInterface::class));
        $this->container->instance('response.factory', $this->container->make(ResponseFactory::class));
        $this->container->instance('exception.handler', $this->container->make(ExceptionHandlerInterface::class));
        $this->container->instance('middleware.registry', $this->container->make(MiddlewareRegistry::class));
        $this->container->instance('route.bindings', $this->container->make(RouteBindingRegistry::class));
        $this->container->instance('controller.dispatcher', $this->container->make(ControllerDispatcher::class));
        $this->container->instance('validator', $this->container->make(ValidatorInterface::class));
        $this->container->instance('actions', $this->container->make(ActionDispatcher::class));
        $this->middleware()->alias('spa', HandleSpaRequests::class);
    }

    public function setBasePath(string $basePath): static
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);

        return $this;
    }

    public function basePath(string $path = ''): string
    {
        return $this->joinPath($this->basePath, $path);
    }

    public function configPath(string $path = ''): string
    {
        return $this->joinPath($this->basePath('config'), $path);
    }

    public function environmentPath(string $path = ''): string
    {
        return $this->joinPath($this->basePath, $path);
    }

    public function bootstrapPath(string $path = ''): string
    {
        return $this->joinPath($this->basePath('bootstrap'), $path);
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function config(): ConfigRepository
    {
        return $this->container->make(ConfigRepository::class);
    }

    public function providers(): ProviderRepository
    {
        return $this->providers;
    }

    public function router(): Router
    {
        return $this->container->make(Router::class);
    }

    public function route(string $name, array $parameters = [], array $query = []): string
    {
        return $this->router()->route($name, $parameters, $query);
    }

    public function kernel(): HttpKernel
    {
        return $this->container->make(HttpKernel::class);
    }

    public function spa(): SpaBridgeInterface
    {
        return $this->container->make(SpaBridgeInterface::class);
    }

    public function shareSpaContext(string|SharedContextProviderInterface $provider): static
    {
        $this->spa()->share($provider);

        return $this;
    }

    public function responses(): ResponseFactory
    {
        return $this->container->make(ResponseFactory::class);
    }

    public function controllers(): ControllerDispatcher
    {
        return $this->container->make(ControllerDispatcher::class);
    }

    public function bindings(): RouteBindingRegistry
    {
        return $this->container->make(RouteBindingRegistry::class);
    }

    public function bindRouteParameter(string $parameter, callable $resolver): static
    {
        $this->bindings()->bind($parameter, $resolver);

        return $this;
    }

    public function bindRouteType(string $type, callable $resolver): static
    {
        $this->bindings()->bindType($type, $resolver);

        return $this;
    }

    public function exceptions(): ExceptionHandlerInterface
    {
        return $this->container->make(ExceptionHandlerInterface::class);
    }

    public function middleware(): MiddlewareRegistry
    {
        return $this->container->make(MiddlewareRegistry::class);
    }

    public function validator(): ValidatorInterface
    {
        return $this->container->make(ValidatorInterface::class);
    }

    public function actions(): ActionDispatcher
    {
        return $this->container->make(ActionDispatcher::class);
    }

    public function register(string $provider): static
    {
        $this->providers->add($provider);

        return $this;
    }

    public function boot(): static
    {
        if ($this->booted) {
            return $this;
        }

        (new BootstrapManager($this))->bootstrap();
        $this->booted = true;

        return $this;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    protected function joinPath(string $base, string $path = ''): string
    {
        if ($path === '') {
            return $base;
        }

        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}
