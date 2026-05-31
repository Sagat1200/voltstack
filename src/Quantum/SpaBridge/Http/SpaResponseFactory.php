<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Http;

use Quantum\Http\Response;
use Quantum\SpaBridge\Contracts\SpaPageInterface;
use Quantum\SpaBridge\Contracts\SpaPayloadInterface;

final class SpaResponseFactory
{
    public function payload(SpaPayloadInterface $payload): Response
    {
        return Response::json($payload->toArray(), $payload->status());
    }

    public function page(SpaPageInterface $page): Response
    {
        return $this->payload($page->toPayload());
    }
}
