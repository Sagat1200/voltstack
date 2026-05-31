<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Support;

use InvalidArgumentException;

final class ComponentName
{
    public static function normalize(string $component): string
    {
        $normalized = trim(str_replace('\\', '/', $component), '/');

        if ($normalized === '') {
            throw new InvalidArgumentException('SPA component name cannot be empty.');
        }

        if (preg_match('#^[A-Za-z0-9_\-]+(?:/[A-Za-z0-9_\-]+)*$#', $normalized) !== 1) {
            throw new InvalidArgumentException(sprintf(
                'Invalid SPA component name [%s].',
                $component,
            ));
        }

        return $normalized;
    }
}