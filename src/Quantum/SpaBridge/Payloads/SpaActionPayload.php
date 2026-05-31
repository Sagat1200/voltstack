<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Payloads;

final class SpaActionPayload extends AbstractSpaPayload
{
    public function __construct(
        array $data = [],
        array $meta = [],
        int $status = 200,
        ?string $message = null,
        ?bool $success = null,
        array $context = [],
        ?string $requestId = null,
        ?int $timestamp = null,
    ) {
        parent::__construct(
            type: 'spa.action',
            status: $status,
            success: $success ?? $status < 400,
            meta: $meta,
            context: $context,
            extra: [
                'data' => $data,
                'message' => $message,
            ],
            requestId: $requestId,
            timestamp: $timestamp,
        );
    }
}
