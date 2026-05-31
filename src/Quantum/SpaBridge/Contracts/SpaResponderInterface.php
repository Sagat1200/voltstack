<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Contracts;

use Quantum\Http\Response;

interface SpaResponderInterface
{
    public function page(string $component, array $props = [], array $meta = []): Response;

    public function action(array $data = [], array $meta = [], int $status = 200, ?string $message = null): Response;

    public function validation(array $errors, array $meta = [], int $status = 422): Response;

    public function error(string $message, int $status = 500, array $meta = [], array $errors = []): Response;

    public function redirect(string $to, int $status = 302, array $meta = [], bool $replace = true): Response;

    public function payload(SpaPayloadInterface $payload): Response;
}
