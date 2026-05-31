<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Payloads;

use Quantum\SpaBridge\Contracts\SpaPayloadInterface;
use Quantum\SpaBridge\Support\ProtocolVersion;
use Quantum\SpaBridge\Support\RequestIdGenerator;

abstract class AbstractSpaPayload implements SpaPayloadInterface
{
    public function __construct(
        protected string $type,
        protected int $status = 200,
        protected bool $success = true,
        protected ?string $component = null,
        protected array $props = [],
        protected array $state = [],
        protected array $meta = [],
        protected array $errors = [],
        protected ?array $redirect = null,
        protected array $partials = [],
        protected array $events = [],
        protected array $context = [],
        protected array $extra = [],
        protected ?string $requestId = null,
        protected ?int $timestamp = null,
    ) {
        $this->requestId ??= RequestIdGenerator::generate();
        $this->timestamp ??= time();
    }

    public function type(): string
    {
        return $this->type;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return array_merge([
            'type' => $this->type,
            'version' => ProtocolVersion::current(),
            'request_id' => $this->requestId,
            'timestamp' => $this->timestamp,
            'status' => $this->status,
            'success' => $this->success,
            'component' => $this->component,
            'props' => $this->props,
            'state' => $this->state,
            'meta' => $this->meta,
            'errors' => $this->errors,
            'redirect' => $this->redirect,
            'partials' => $this->partials,
            'events' => $this->events,
            'context' => $this->context,
        ], $this->extraPayload());
    }

    protected function extraPayload(): array
    {
        return $this->extra;
    }
}
