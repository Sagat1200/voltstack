<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Payloads;

final class SpaPagePayload extends AbstractSpaPayload
{
    public function __construct(
        string $component,
        array $props = [],
        array $meta = [],
        int $status = 200,
        array $state = [],
        array $context = [],
        ?string $requestId = null,
        ?int $timestamp = null,
    ) {
        parent::__construct(
            type: 'spa.page',
            status: $status,
            success: $status < 400,
            component: $component,
            props: $props,
            state: $state,
            meta: $meta,
            context: $context,
            requestId: $requestId,
            timestamp: $timestamp,
        );
    }
}
