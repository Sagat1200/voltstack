<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Contracts;

interface SpaPayloadInterface
{
    public function type(): string;

    public function status(): int;

    public function toArray(): array;
}