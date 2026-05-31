<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Payloads;

final class SpaValidationPayload extends AbstractSpaPayload
{
    public function __construct(
        array $errors,
        array $meta = [],
        int $status = 422,
        array $context = [],
        ?string $requestId = null,
        ?int $timestamp = null,
    ) {
        parent::__construct(
            type: 'spa.validation',
            status: $status,
            success: false,
            meta: $meta,
            errors: $errors,
            context: $context,
            requestId: $requestId,
            timestamp: $timestamp,
        );
    }
}
