<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Pages;

use Quantum\SpaBridge\SpaPage;

final class PageDefinition
{
    public function __construct(
        protected string $component,
        protected array $props = [],
        protected array $meta = [],
        protected array $context = [],
    ) {}

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

    public function context(): array
    {
        return $this->context;
    }

    public function toPage(): SpaPage
    {
        return new SpaPage($this->component, $this->props, $this->meta, $this->context);
    }
}