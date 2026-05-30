<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Validation;

use Quantum\Validation\ValidationException;
use Quantum\Validation\Validator;
use VoltStack\Framework\Tests\TestCase;

final class ValidatorTest extends TestCase
{
    public function test_validator_accepts_valid_payload(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'email' => 'user@example.com',
            'name' => 'VoltStack',
            'age' => '18',
        ], [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:3'],
            'age' => ['integer', 'min:18'],
        ]);

        self::assertSame('user@example.com', $validated['email']);
        self::assertSame('VoltStack', $validated['name']);
        self::assertSame('18', $validated['age']);
    }

    public function test_validator_throws_validation_exception_with_errors(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'email' => 'not-an-email',
                'name' => 'ab',
            ], [
                'email' => ['required', 'email'],
                'name' => ['required', 'string', 'min:3'],
                'password' => ['required'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->statusCode());
            self::assertArrayHasKey('email', $exception->errors());
            self::assertArrayHasKey('name', $exception->errors());
            self::assertArrayHasKey('password', $exception->errors());
        }
    }

    public function test_validator_supports_custom_messages_and_attributes(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'email' => 'not-an-email',
            ], [
                'email' => ['required', 'email'],
                'name' => ['required'],
            ], [
                'email.email' => 'Debes indicar un :attribute valido.',
                'required' => 'El campo :attribute es obligatorio.',
            ], [
                'email' => 'correo electronico',
                'name' => 'nombre completo',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'email' => ['Debes indicar un correo electronico valido.'],
                'name' => ['El campo nombre completo es obligatorio.'],
            ], $exception->errors());
        }
    }
}
