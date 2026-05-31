<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Contracts;

interface SpaPageInterface
{
    public function component(): string;

    public function props(): array;

    public function meta(): array;

    public function toPayload(): SpaPayloadInterface;
}
