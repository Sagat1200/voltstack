<?php

declare(strict_types=1);

namespace Quantum\SpaBridge;

use Quantum\Http\Response;
use Quantum\SpaBridge\Contracts\SpaBridgeInterface;
use Quantum\SpaBridge\Contracts\SpaPageInterface;
use Quantum\SpaBridge\Contracts\SpaPayloadInterface;
use Quantum\SpaBridge\Contracts\SpaResponderInterface;

final class SpaBridge implements SpaBridgeInterface
{
    protected SpaResponderInterface $responder;

    public function __construct(?SpaResponderInterface $responder = null)
    {
        $this->responder = $responder ?? new SpaResponder();
    }

    public function page(string $component, array $props = [], array $meta = []): SpaPageInterface
    {
        return new SpaPage($component, $props, $meta);
    }

    public function payload(SpaPayloadInterface $payload): Response
    {
        return $this->responder->payload($payload);
    }

    public function responder(): SpaResponderInterface
    {
        return $this->responder;
    }
}
