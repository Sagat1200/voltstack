<?php

declare(strict_types=1);

namespace Quantum\Http;

final class ResponseFactory
{
    public function __construct(
        protected $normalizer = null,
    ) {}

    public function make(string $content = '', int $status = 200, array $headers = []): Response
    {
        return Response::make($content, $status, $headers);
    }

    public function json(mixed $data, int $status = 200, array $headers = []): Response
    {
        return Response::json($data, $status, $headers);
    }

    public function text(string $content, int $status = 200, array $headers = []): Response
    {
        return Response::make($content, $status, $headers);
    }

    public function empty(int $status = 204, array $headers = []): Response
    {
        return Response::make('', $status, $headers);
    }

    public function notFound(string $content = 'Not Found', array $headers = []): Response
    {
        return Response::make($content, 404, $headers);
    }

    public function from(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_callable($this->normalizer)) {
            $normalized = ($this->normalizer)($result);

            if ($normalized instanceof Response) {
                return $normalized;
            }
        }

        if (is_array($result) || is_object($result)) {
            return $this->json($result);
        }

        if ($result === null) {
            return $this->make('');
        }

        return $this->make((string) $result);
    }
}