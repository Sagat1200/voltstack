<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\SpaBridge;

use Quantum\Http\Request;
use Quantum\SpaBridge\Context\Contracts\SharedContextProviderInterface;
use VoltStack\Framework\Tests\TestCase;

final class SpaHttpIntegrationTest extends TestCase
{
    public function test_response_factory_normalizes_spa_pages_and_payloads(): void
    {
        $app = $this->createApplication();
        $app->shareSpaContext(HttpIntegrationSharedContextProvider::class);

        $page = $app->spa()->page('Dashboard/Home', [
            'stats' => ['users' => 10],
        ], [
            'layout' => 'main',
        ]);

        $pageResponse = $app->responses()->from($page);
        $payloadResponse = $app->responses()->from($page->toPayload());
        $pageData = json_decode($pageResponse->content(), true, 512, JSON_THROW_ON_ERROR);
        $payloadData = json_decode($payloadResponse->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('application/json', $pageResponse->header('Content-Type'));
        self::assertSame('spa.page', $pageData['type']);
        self::assertSame('Dashboard/Home', $pageData['component']);
        self::assertSame(['layout' => 'main'], $pageData['meta']);
        self::assertSame([
            'tenant' => [
                'id' => 77,
                'slug' => 'acme',
            ],
        ], $pageData['context']);

        self::assertSame('application/json', $payloadResponse->header('Content-Type'));
        self::assertSame('spa.page', $payloadData['type']);
        self::assertSame('Dashboard/Home', $payloadData['component']);
    }

    public function test_http_kernel_normalizes_spa_pages_returned_by_route_handlers(): void
    {
        $app = $this->createApplication();
        $app->shareSpaContext(HttpIntegrationSharedContextProvider::class);
        $app->router()->get('/spa/users', fn() => $app->spa()->page('Users/Index', [
            'users' => [['id' => 1]],
        ], [
            'title' => 'Users',
        ]));

        $response = $app->kernel()->handle(Request::create('GET', '/spa/users'));
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('spa.page', $data['type']);
        self::assertSame('Users/Index', $data['component']);
        self::assertSame(['users' => [['id' => 1]]], $data['props']);
        self::assertSame([
            'tenant' => [
                'id' => 77,
                'slug' => 'acme',
            ],
        ], $data['context']);
    }

    public function test_spa_middleware_exposes_request_metadata_attributes(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/spa/meta', fn(Request $request) => [
            'spa' => [
                'request' => $request->attribute('spa.request'),
                'target' => $request->attribute('spa.target'),
                'version' => $request->attribute('spa.version'),
                'partials' => $request->attribute('spa.partials'),
            ],
        ])->middleware('spa');

        $response = $app->kernel()->handle(Request::create(
            'GET',
            '/spa/meta',
            [],
            [],
            [
                'Accept' => 'application/json',
                'X-Spa' => 'true',
                'X-Spa-Target' => 'Users/Index',
                'X-Spa-Version' => '2.0.0',
                'X-Spa-Partials' => 'users, stats',
            ],
        ));
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame([
            'spa' => [
                'request' => true,
                'target' => 'Users/Index',
                'version' => '2.0.0',
                'partials' => ['users', 'stats'],
            ],
        ], $data);
    }
}

final class HttpIntegrationSharedContextProvider implements SharedContextProviderInterface
{
    public function provide(): array
    {
        return [
            'tenant' => [
                'id' => 77,
                'slug' => 'acme',
            ],
        ];
    }
}
