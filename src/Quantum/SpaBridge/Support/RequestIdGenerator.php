<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Support;

final class RequestIdGenerator
{
    public static function generate(): string
    {
        return 'req_' . bin2hex(random_bytes(6));
    }
}
