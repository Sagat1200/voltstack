<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Support;

final class ProtocolVersion
{
    public const CURRENT = '1.0.0';

    public static function current(): string
    {
        return self::CURRENT;
    }
}
