<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Http;

use LogicException;
use Quantum\Http\ValidatedInput;
use VoltStack\Framework\Tests\TestCase;

final class ValidatedInputTest extends TestCase
{
    public function test_validated_input_supports_array_access_and_dot_notation(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
            ],
        ]);

        self::assertTrue(isset($input['email']));
        self::assertTrue(isset($input['profile.name']));
        self::assertSame('user@example.com', $input['email']);
        self::assertSame('VoltStack', $input['profile.name']);
        self::assertNull($input['missing']);
    }

    public function test_validated_input_is_iterable_and_json_serializable(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
            ],
        ]);

        self::assertSame([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
            ],
        ], iterator_to_array($input));

        self::assertSame(
            '{"email":"user@example.com","profile":{"name":"VoltStack"}}',
            json_encode($input, JSON_THROW_ON_ERROR)
        );
    }

    public function test_validated_input_supports_presence_helpers_and_counting(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
                'nickname' => '',
            ],
            'active' => false,
            'score' => 0,
            'tags' => [],
        ]);

        self::assertTrue($input->has('email'));
        self::assertTrue($input->has(['email', 'profile.name']));
        self::assertFalse($input->has(['email', 'missing']));
        self::assertTrue($input->missing('missing'));
        self::assertFalse($input->missing('profile.name'));

        self::assertTrue($input->filled(['email', 'profile.name']));
        self::assertTrue($input->filled(['active', 'score']));
        self::assertFalse($input->filled('profile.nickname'));
        self::assertFalse($input->filled('tags'));
        self::assertFalse($input->filled('missing'));

        self::assertSame(5, $input->count());
        self::assertFalse($input->isEmpty());
        self::assertSame(0, (new ValidatedInput([]))->count());
        self::assertTrue((new ValidatedInput([]))->isEmpty());
    }

    public function test_validated_input_is_read_only(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ValidatedInput is read-only.');

        $input['email'] = 'other@example.com';
    }
}
