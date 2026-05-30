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

    public function test_validator_supports_nullable_array_boolean_and_max_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'nickname' => null,
            'flags' => ['a', 'b'],
            'active' => '1',
            'title' => 'Volt',
            'score' => 10,
        ], [
            'nickname' => ['nullable', 'string', 'max:10'],
            'flags' => ['required', 'array', 'max:3'],
            'active' => ['required', 'boolean'],
            'title' => ['required', 'string', 'max:4'],
            'score' => ['required', 'max:10'],
        ]);

        self::assertSame([
            'nickname' => null,
            'flags' => ['a', 'b'],
            'active' => '1',
            'title' => 'Volt',
            'score' => 10,
        ], $validated);
    }

    public function test_validator_throws_errors_for_array_boolean_and_max_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'flags' => 'not-an-array',
                'active' => 'true',
                'title' => 'VoltStack',
                'score' => 11,
            ], [
                'flags' => ['required', 'array'],
                'active' => ['required', 'boolean'],
                'title' => ['required', 'string', 'max:4'],
                'score' => ['required', 'max:10'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'flags' => ['The flags field must be an array.'],
                'active' => ['The active field must be true or false.'],
                'title' => ['The title field may not be greater than 4.'],
                'score' => ['The score field may not be greater than 10.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_confirmed_same_in_and_numeric_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'pin' => '1234',
            'pin_repeat' => '1234',
            'status' => 'published',
            'price' => '19.99',
        ], [
            'password' => ['required', 'confirmed'],
            'pin' => ['required', 'same:pin_repeat'],
            'status' => ['required', 'in:draft,published,archived'],
            'price' => ['required', 'numeric'],
        ]);

        self::assertSame([
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'pin' => '1234',
            'pin_repeat' => '1234',
            'status' => 'published',
            'price' => '19.99',
        ], $validated);
    }

    public function test_validator_throws_errors_for_confirmed_same_in_and_numeric_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'password' => 'secret123',
                'password_confirmation' => 'different',
                'pin' => '1234',
                'pin_repeat' => '0000',
                'status' => 'pending',
                'price' => 'not-a-number',
            ], [
                'password' => ['required', 'confirmed'],
                'pin' => ['required', 'same:pin_repeat'],
                'status' => ['required', 'in:draft,published,archived'],
                'price' => ['required', 'numeric'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'password' => ['The password field confirmation does not match.'],
                'pin' => ['The pin field and pin_repeat must match.'],
                'status' => ['The selected status is invalid.'],
                'price' => ['The price field must be a number.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_accepted_url_date_and_alpha_dash_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'terms' => 'yes',
            'website' => 'https://voltstack.dev/docs',
            'published_at' => '2026-05-29 10:30:00',
            'slug' => 'voltstack_docs-v1',
        ], [
            'terms' => ['required', 'accepted'],
            'website' => ['required', 'url'],
            'published_at' => ['required', 'date'],
            'slug' => ['required', 'alpha_dash'],
        ]);

        self::assertSame([
            'terms' => 'yes',
            'website' => 'https://voltstack.dev/docs',
            'published_at' => '2026-05-29 10:30:00',
            'slug' => 'voltstack_docs-v1',
        ], $validated);
    }

    public function test_validator_throws_errors_for_accepted_url_date_and_alpha_dash_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'terms' => 'no',
                'website' => 'not-a-url',
                'published_at' => 'not-a-date',
                'slug' => 'voltstack docs',
            ], [
                'terms' => ['required', 'accepted'],
                'website' => ['required', 'url'],
                'published_at' => ['required', 'date'],
                'slug' => ['required', 'alpha_dash'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'terms' => ['The terms field must be accepted.'],
                'website' => ['The website field must be a valid URL.'],
                'published_at' => ['The published_at field must be a valid date.'],
                'slug' => ['The slug field may only contain letters, numbers, dashes, and underscores.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_nested_fields_and_wildcards(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'profile' => [
                'name' => 'VoltStack',
            ],
            'items' => [
                ['name' => 'core_api', 'price' => '10.5'],
                ['name' => 'admin-ui', 'price' => 20],
            ],
        ], [
            'profile.name' => ['required', 'string', 'min:3'],
            'items.*.name' => ['required', 'alpha_dash'],
            'items.*.price' => ['required', 'numeric'],
        ]);

        self::assertSame([
            'profile' => [
                'name' => 'VoltStack',
            ],
            'items' => [
                ['name' => 'core_api', 'price' => '10.5'],
                ['name' => 'admin-ui', 'price' => 20],
            ],
        ], $validated);
    }

    public function test_validator_supports_nested_messages_and_attributes_with_wildcards(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'profile' => [],
                'items' => [
                    ['name' => 'core api', 'price' => '10.5'],
                    ['price' => 'oops'],
                ],
            ], [
                'profile.name' => ['required'],
                'items.*.name' => ['required', 'alpha_dash'],
                'items.*.price' => ['required', 'numeric'],
            ], [
                'profile.name.required' => 'Debes completar :attribute.',
                'items.*.name.required' => 'Cada :attribute es obligatorio.',
                'items.*.name.alpha_dash' => 'Cada :attribute solo admite letras, numeros, guiones y guion bajo.',
                'items.*.price.numeric' => 'Cada :attribute debe ser numerico.',
            ], [
                'profile.name' => 'nombre del perfil',
                'items.*.name' => 'nombre del item',
                'items.*.price' => 'precio del item',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'profile.name' => ['Debes completar nombre del perfil.'],
                'items.0.name' => ['Cada nombre del item solo admite letras, numeros, guiones y guion bajo.'],
                'items.1.name' => ['Cada nombre del item es obligatorio.'],
                'items.1.price' => ['Cada precio del item debe ser numerico.'],
            ], $exception->errors());
        }
    }
}
