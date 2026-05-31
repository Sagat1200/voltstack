<?php

declare(strict_types=1);

namespace Quantum\SpaBridge;

use Closure;
use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;
use Quantum\SpaBridge\Adapters\Contracts\FrontendAdapterInterface;
use Quantum\SpaBridge\Adapters\NullFrontendAdapter;
use Quantum\SpaBridge\Context\Contracts\SharedContextResolverInterface;
use Quantum\SpaBridge\Context\SharedContextResolver;
use Quantum\SpaBridge\Contracts\SpaPayloadInterface;
use Quantum\SpaBridge\Contracts\SpaResponderInterface;
use Quantum\SpaBridge\Metadata\Contracts\FrontendMetadataFactoryInterface;
use Quantum\SpaBridge\Metadata\FrontendMetadataFactory;
use Quantum\SpaBridge\Pages\Contracts\PageResolverInterface;
use Quantum\SpaBridge\Pages\PageResolver;
use Quantum\SpaBridge\Payloads\SpaActionPayload;
use Quantum\SpaBridge\Payloads\SpaErrorPayload;
use Quantum\SpaBridge\Payloads\SpaRedirectPayload;
use Quantum\SpaBridge\Payloads\SpaValidationPayload;

final class SpaResponder implements SpaResponderInterface
{
    public function __construct(
        protected ResponseFactory $responses = new ResponseFactory(),
        protected SharedContextResolverInterface $contextResolver = new SharedContextResolver(),
        protected PageResolverInterface $pages = new PageResolver(),
        protected FrontendMetadataFactoryInterface $frontend = new FrontendMetadataFactory(),
        FrontendAdapterInterface|Closure|null $adapter = null,
    ) {
        $this->resolveAdapter = match (true) {
            $adapter instanceof Closure => $adapter,
            $adapter instanceof FrontendAdapterInterface => static fn(): FrontendAdapterInterface => $adapter,
            default => static fn(): FrontendAdapterInterface => new NullFrontendAdapter(),
        };
    }

    protected Closure $resolveAdapter;

    public function page(string $component, array $props = [], array $meta = []): Response
    {
        return $this->payload(
            $this->pages->resolve(
                $component,
                $props,
                $this->frontend->make($meta, $this->adapter(), $component),
                $this->contextResolver->resolve()
            )->toPage()->toPayload()
        );
    }

    public function action(array $data = [], array $meta = [], int $status = 200, ?string $message = null): Response
    {
        return $this->payload(new SpaActionPayload(
            $data,
            $this->frontend->make($meta, $this->adapter()),
            $status,
            $message,
            context: $this->contextResolver->resolve()
        ));
    }

    public function validation(array $errors, array $meta = [], int $status = 422): Response
    {
        return $this->payload(new SpaValidationPayload(
            $errors,
            $this->frontend->make($meta, $this->adapter()),
            $status,
            $this->contextResolver->resolve()
        ));
    }

    public function error(string $message, int $status = 500, array $meta = [], array $errors = []): Response
    {
        return $this->payload(new SpaErrorPayload(
            $message,
            $status,
            $this->frontend->make($meta, $this->adapter()),
            $errors,
            $this->contextResolver->resolve()
        ));
    }

    public function redirect(string $to, int $status = 302, array $meta = [], bool $replace = true): Response
    {
        return $this->payload(new SpaRedirectPayload(
            $to,
            $status,
            $this->frontend->make($meta, $this->adapter()),
            $replace,
            $this->contextResolver->resolve()
        ));
    }

    public function payload(SpaPayloadInterface $payload): Response
    {
        return $this->responses->json($payload->toArray(), $payload->status());
    }

    protected function adapter(): FrontendAdapterInterface
    {
        return ($this->resolveAdapter)();
    }
}
