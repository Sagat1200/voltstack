<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Controllers;

use Quantum\Controllers\Controller;
use Quantum\Http\Request;
use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use Quantum\SpaBridge\Contracts\SpaBridgeInterface;
use Quantum\SpaBridge\Http\Concerns\InteractsWithSpaResponses;
use VoltStack\Framework\Tests\TestCase;

final class SpaControllerTest extends TestCase
{
    public function test_application_exposes_spa_bridge(): void
    {
        $app = $this->createApplication();

        self::assertInstanceOf(SpaBridgeInterface::class, $app->spa());
    }

    public function test_controller_can_return_a_spa_page_response_using_the_trait(): void
    {
        $app = $this->createApplication();
        $app->shareSpaContext(SpaControllerSharedContextProvider::class);
        $app->router()->get('/spa/dashboard', SpaDashboardController::class . '@show');

        $response = $app->kernel()->handle(Request::create('GET', '/spa/dashboard'));
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('spa.page', $data['type']);
        self::assertSame('1.0.0', $data['version']);
        self::assertSame('Dashboard/Home', $data['component']);
        self::assertSame(['stats' => ['users' => 10]], $data['props']);
        self::assertSame('main', $data['meta']['layout']);
        self::assertSame('/spa/dashboard', $data['meta']['navigation']['url']);
        self::assertSame('/spa/dashboard', $data['meta']['navigation']['path']);
        self::assertSame('null', $data['meta']['frontend']['adapter']['name']);
        self::assertSame('0.0.0', $data['meta']['frontend']['adapter']['version']);
        self::assertSame([], $data['meta']['frontend']['entrypoints']);
        self::assertSame(200, $data['status']);
        self::assertTrue($data['success']);
        self::assertSame([], $data['errors']);
        self::assertNull($data['redirect']);
        self::assertSame([
            'auth' => [
                'user' => [
                    'id' => 10,
                    'name' => 'Volt User',
                ],
            ],
            'locale' => [
                'current' => 'es',
            ],
        ], $data['context']);
        self::assertIsString($data['request_id']);
        self::assertIsInt($data['timestamp']);
    }
}

final class SpaControllerSharedContextProvider implements SharedContextProviderInterface
{
    public function provide(): array
    {
        return [
            'auth' => [
                'user' => [
                    'id' => 10,
                    'name' => 'Volt User',
                ],
            ],
            'locale' => [
                'current' => 'es',
            ],
        ];
    }
}

final class SpaDashboardController extends Controller
{
    use InteractsWithSpaResponses;

    public function show()
    {
        return $this->spa('Dashboard/Home', [
            'stats' => ['users' => 10],
        ], [
            'layout' => 'main',
        ]);
    }
}
