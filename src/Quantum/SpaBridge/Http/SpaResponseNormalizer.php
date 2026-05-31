<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Http;

use Quantum\Http\Response;
use Quantum\SpaBridge\Contracts\SpaPageInterface;
use Quantum\SpaBridge\Contracts\SpaPayloadInterface;

final class SpaResponseNormalizer
{
    public function __construct(
        protected SpaResponseFactory $responses = new SpaResponseFactory(),
    ) {
    }

    public function normalize(mixed $result): ?Response
    {
        if ($result instanceof SpaPayloadInterface) {
            return $this->responses->payload($result);
        }

        if ($result instanceof SpaPageInterface) {
            return $this->responses->page($result);
        }

        return null;
    }
}
