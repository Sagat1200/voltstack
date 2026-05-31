<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\SpaBridge;

use Quantum\Exceptions\HttpException;
use Quantum\Http\Request;
use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use Quantum\Validation\ValidationException;
use VoltStack\Framework\Tests\TestCase;

final class SpaExceptionHandlingTest extends TestCase
{
    public function test_spa_middleware_converts_validation_exception_to_spa_validation_payload(): void
    {
        $app = $this->createApplication();
        $app->shareSpaContext(SpaExceptionSharedContextProvider::class);
        $app->router()->get('/spa/validation', static function (): never {
            throw new ValidationException([
                'email' => ['The email field is required.'],
            ]);
        })->middleware('spa');

        $response = $app->kernel()->handle(Request::create(
            'GET',
            '/spa/validation',
            [],
            [],
            [
                'Accept' => 'application/json',
                'X-Spa' => 'true',
            ],
        ));
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(422, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('spa.validation', $data['type']);
        self::assertSame(422, $data['status']);
        self::assertFalse($data['success']);
        self::assertSame([
            'email' => ['The email field is required.'],
        ], $data['errors']);
        self::assertSame([
            'auth' => [
                'user' => [
                    'id' => 15,
                    'name' => 'Spa Tester',
                ],
            ],
        ], $data['context']);
    }

    public function test_spa_middleware_converts_http_exception_to_spa_error_payload(): void
    {
        $app = $this->createApplication();
        $app->shareSpaContext(SpaExceptionSharedContextProvider::class);
        $app->router()->get('/spa/forbidden', static function (): never {
            throw new HttpException(403, 'Forbidden');
        })->middleware('spa');

        $response = $app->kernel()->handle(Request::create(
            'GET',
            '/spa/forbidden',
            [],
            [],
            [
                'Accept' => 'application/json',
                'X-Spa' => 'true',
            ],
        ));
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(403, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('spa.error', $data['type']);
        self::assertSame(403, $data['status']);
        self::assertFalse($data['success']);
        self::assertSame('Forbidden', $data['message']);
        self::assertSame([
            'auth' => [
                'user' => [
                    'id' => 15,
                    'name' => 'Spa Tester',
                ],
            ],
        ], $data['context']);
    }
}

final class SpaExceptionSharedContextProvider implements SharedContextProviderInterface
{
    public function provide(): array
    {
        return [
            'auth' => [
                'user' => [
                    'id' => 15,
                    'name' => 'Spa Tester',
                ],
            ],
        ];
    }
}
