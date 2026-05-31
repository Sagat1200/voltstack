<?php

declare(strict_types=1);

namespace Quantum\SpaBridge;

use Quantum\SpaBridge\Contracts\SpaPageInterface;
use Quantum\SpaBridge\Contracts\SpaPayloadInterface;
use Quantum\SpaBridge\Payloads\SpaPagePayload;

final class SpaPage implements SpaPageInterface
{
    public function __construct(
        protected string $component,
        protected array $props = [],
        protected array $meta = [],
    ) {
    }

    public function component(): string
    {
        return $this->component;
    }

    public function props(): array
    {
        return $this->props;
    }

    public function meta(): array
    {
        return $this->meta;
    }

    public function toPayload(): SpaPayloadInterface
    {
        return new SpaPagePayload($this->component, $this->props, $this->meta);
    }
}
