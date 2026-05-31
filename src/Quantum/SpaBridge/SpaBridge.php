<?php

declare(strict_types=1);

namespace Quantum\SpaBridge;

use Quantum\Http\Response;
use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextRegistryInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextResolverInterface;
use Quantum\SpaBridge\Context\SharedContextRegistry;
use Quantum\SpaBridge\Context\SharedContextResolver;
use Quantum\SpaBridge\Contracts\SpaBridgeInterface;
use Quantum\SpaBridge\Contracts\SpaPageInterface;
use Quantum\SpaBridge\Contracts\SpaPayloadInterface;
use Quantum\SpaBridge\Contracts\SpaResponderInterface;

final class SpaBridge implements SpaBridgeInterface
{
    protected SpaResponderInterface $responder;
    protected SharedContextResolverInterface $contextResolver;

    public function __construct(
        ?SpaResponderInterface $responder = null,
        protected SharedContextRegistryInterface $contextRegistry = new SharedContextRegistry(),
        ?SharedContextResolverInterface $contextResolver = null,
    ) {
        $this->contextResolver = $contextResolver ?? new SharedContextResolver($this->contextRegistry);
        $this->responder = $responder ?? new SpaResponder(contextResolver: $this->contextResolver);
    }

    public function page(string $component, array $props = [], array $meta = []): SpaPageInterface
    {
        return new SpaPage($component, $props, $meta, $this->context());
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
}
