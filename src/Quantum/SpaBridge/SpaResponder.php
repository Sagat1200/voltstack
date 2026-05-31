<?php

declare(strict_types=1);

namespace Quantum\SpaBridge;

use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;
use Quantum\SpaBridge\Context\Contracts\SharedContextResolverInterface;
use Quantum\SpaBridge\Context\SharedContextResolver;
use Quantum\SpaBridge\Contracts\SpaPayloadInterface;
use Quantum\SpaBridge\Contracts\SpaResponderInterface;
use Quantum\SpaBridge\Payloads\SpaActionPayload;
use Quantum\SpaBridge\Payloads\SpaErrorPayload;
use Quantum\SpaBridge\Payloads\SpaRedirectPayload;
use Quantum\SpaBridge\Payloads\SpaValidationPayload;

final class SpaResponder implements SpaResponderInterface
{
    public function __construct(
        protected ResponseFactory $responses = new ResponseFactory(),
        protected SharedContextResolverInterface $contextResolver = new SharedContextResolver(),
    ) {}

    public function page(string $component, array $props = [], array $meta = []): Response
    {
        return $this->payload((new SpaPage($component, $props, $meta, $this->contextResolver->resolve()))->toPayload());
    }

    public function action(array $data = [], array $meta = [], int $status = 200, ?string $message = null): Response
    {
        return $this->payload(new SpaActionPayload($data, $meta, $status, $message, context: $this->contextResolver->resolve()));
    }

    public function validation(array $errors, array $meta = [], int $status = 422): Response
    {
        return $this->payload(new SpaValidationPayload($errors, $meta, $status, $this->contextResolver->resolve()));
    }

    public function error(string $message, int $status = 500, array $meta = [], array $errors = []): Response
    {
        return $this->payload(new SpaErrorPayload($message, $status, $meta, $errors, $this->contextResolver->resolve()));
    }

    public function redirect(string $to, int $status = 302, array $meta = [], bool $replace = true): Response
    {
        return $this->payload(new SpaRedirectPayload($to, $status, $meta, $replace, $this->contextResolver->resolve()));
    }

    public function payload(SpaPayloadInterface $payload): Response
    {
        return $this->responses->json($payload->toArray(), $payload->status());
    }
}
