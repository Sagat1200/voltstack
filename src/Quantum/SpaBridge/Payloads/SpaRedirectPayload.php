<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Payloads;

final class SpaRedirectPayload extends AbstractSpaPayload
{
    public function __construct(
        string $to,
        int $status = 302,
        array $meta = [],
        bool $replace = true,
        array $context = [],
        ?string $requestId = null,
        ?int $timestamp = null,
    ) {
        parent::__construct(
            type: 'spa.redirect',
            status: $status,
            success: $status < 400,
            meta: $meta,
            redirect: [
                'to' => $to,
                'replace' => $replace,
            ],
            context: $context,
            requestId: $requestId,
            timestamp: $timestamp,
        );
    }
}
