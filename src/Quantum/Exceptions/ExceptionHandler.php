<?php

declare(strict_types=1);

namespace Quantum\Exceptions;

use Quantum\Exceptions\Contracts\ExceptionHandlerInterface;
use Quantum\Http\Request;
use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;
use Quantum\SpaBridge\Context\Contracts\SharedContextResolverInterface;
use Quantum\SpaBridge\Context\SharedContextResolver;
use Quantum\SpaBridge\Http\SpaResponseFactory;
use Quantum\SpaBridge\Payloads\SpaErrorPayload;
use Quantum\SpaBridge\Payloads\SpaValidationPayload;
use Quantum\Validation\ValidationException;
use Throwable;

final class ExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        protected ResponseFactory $responses = new ResponseFactory(),
        protected SpaResponseFactory $spaResponses = new SpaResponseFactory(),
        protected SharedContextResolverInterface $contextResolver = new SharedContextResolver(),
    ) {}

    public function report(Throwable $throwable): void
    {
        // Reporting hooks can be added later without changing the kernel contract.
    }

    public function render(Request $request, Throwable $throwable): Response
    {
        $status = 500;
        $headers = [];
        $message = 'Server Error';

        if ($throwable instanceof HttpException) {
            $status = $throwable->statusCode();
            $headers = $throwable->headers();
            $message = $throwable->getMessage();
        }

        if ($this->expectsSpa($request)) {
            return $this->withHeaders(
                $this->renderSpa($throwable, $message, $status),
                $headers,
            );
        }

        if ($this->expectsJson($request)) {
            $payload = [
                'message' => $message,
                'status' => $status,
            ];

            if ($throwable instanceof ValidationException) {
                $payload['errors'] = $throwable->errors();
            }

            return $this->responses->json($payload, $status, $headers);
        }

        return $this->responses->make($message, $status, $headers);
    }

    protected function renderSpa(Throwable $throwable, string $message, int $status): Response
    {
        $context = $this->contextResolver->resolve();

        if ($throwable instanceof ValidationException) {
            return $this->spaResponses->payload(new SpaValidationPayload(
                $throwable->errors(),
                status: $status,
                context: $context,
            ));
        }

        return $this->spaResponses->payload(new SpaErrorPayload(
            $message,
            $status,
            context: $context,
        ));
    }

    protected function expectsSpa(Request $request): bool
    {
        return $request->attribute('spa.request', false) === true
            || filter_var($request->header('x-spa', false), FILTER_VALIDATE_BOOL);
    }

    protected function expectsJson(Request $request): bool
    {
        $accept = (string) $request->header('accept', '');

        return str_contains(strtolower($accept), 'application/json');
    }

    protected function withHeaders(Response $response, array $headers): Response
    {
        foreach ($headers as $key => $value) {
            $response = $response->withHeader((string) $key, (string) $value);
        }

        return $response;
    }
}