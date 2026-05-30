<?php

declare(strict_types=1);

namespace Quantum\Validation\Contracts;

use Quantum\Validation\ValidationException;

interface ValidatorInterface
{
    /**
     * @throws ValidationException
     */
    public function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array;
}
