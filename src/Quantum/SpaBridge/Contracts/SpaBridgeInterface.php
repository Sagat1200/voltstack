<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Contracts;

use Quantum\Http\Response;
use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;
use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;

interface SpaBridgeInterface
{
    public function page(string $component, array $props = [], array $meta = []): SpaPageInterface;

    public function payload(SpaPayloadInterface $payload): Response;

    public function responder(): SpaResponderInterface;

    public function share(string|SharedContextProviderInterface $provider): static;

    public function context(): array;

    public function adapter(): FrontendAdapterInterface;
}
