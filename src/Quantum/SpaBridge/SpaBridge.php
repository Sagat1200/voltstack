<?php

declare(strict_types=1);

namespace Quantum\SpaBridge;

use Quantum\Http\Response;
use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;
use Quantum\SpaBridge\Adapters\NullFrontendAdapter;
use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextRegistryInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextResolverInterface;
use Quantum\SpaBridge\Context\SharedContextRegistry;
use Quantum\SpaBridge\Context\SharedContextResolver;
use Quantum\SpaBridge\Contracts\SpaBridgeInterface;
use Quantum\SpaBridge\Contracts\SpaPageInterface;
use Quantum\SpaBridge\Contracts\SpaPayloadInterface;
use Quantum\SpaBridge\Contracts\SpaResponderInterface;
use Quantum\SpaBridge\Pages\Contracts\PageResolverInterface;
use Quantum\SpaBridge\Pages\PageResolver;

final class SpaBridge implements SpaBridgeInterface
{
    protected SpaResponderInterface $responder;
    protected SharedContextResolverInterface $contextResolver;
    protected PageResolverInterface $pages;
    protected FrontendAdapterInterface $adapter;

    public function __construct(
        ?SpaResponderInterface $responder = null,
        protected SharedContextRegistryInterface $contextRegistry = new SharedContextRegistry(),
        ?SharedContextResolverInterface $contextResolver = null,
        ?PageResolverInterface $pages = null,
        ?FrontendAdapterInterface $adapter = null,
    ) {
        $this->contextResolver = $contextResolver ?? new SharedContextResolver($this->contextRegistry);
        $this->pages = $pages ?? new PageResolver();
        $this->adapter = $adapter ?? new NullFrontendAdapter();
        $this->responder = $responder ?? new SpaResponder(contextResolver: $this->contextResolver);
    }

    public function page(string $component, array $props = [], array $meta = []): SpaPageInterface
    {
        return $this->pages->resolve($component, $props, $meta, $this->context())->toPage();
    }

    public function payload(SpaPayloadInterface $payload): Response
    {
        return $this->responder->payload($payload);
    }

    public function responder(): SpaResponderInterface
    {
        return $this->responder;
    }

    public function share(string|SharedContextProviderInterface $provider): static
    {
        $this->contextRegistry->register($provider);

        return $this;
    }

    public function context(): array
    {
        return $this->contextResolver->resolve();
    }

    public function adapter(): FrontendAdapterInterface
    {
        return $this->adapter;
    }
}
