<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Payloads;

final class SpaErrorPayload extends AbstractSpaPayload
{
    public function __construct(
        string $message,
        int $status = 500,
        array $meta = [],
        array $errors = [],
        array $context = [],
        ?string $requestId = null,
        ?int $timestamp = null,
    ) {
        parent::__construct(
            type: 'spa.error',
            status: $status,
            success: false,
            meta: $meta,
            errors: $errors,
            context: $context,
            extra: [
                'message' => $message,
            ],
            requestId: $requestId,
            timestamp: $timestamp,
        );
    }
}
