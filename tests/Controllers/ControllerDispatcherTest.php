<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Controllers;

use Quantum\Controllers\ControllerDispatcher;
use Quantum\Http\Request;
use Quantum\Routing\ResolvedRoute;
use Quantum\Routing\Route;
use VoltStack\Framework\Tests\TestCase;

final class ControllerDispatcherTest extends TestCase
{
    public function test_dispatcher_dispatches_controller_string_and_injects_route_parameter(): void
    {
        $app = $this->createApplication();
        $route = new Route(['GET'], '/posts/{id}', DispatcherController::class . '@show');
        $resolved = new ResolvedRoute($route, ['id' => '15']);

        $result = $app->controllers()->dispatchResolvedRoute($resolved, Request::create('GET', '/posts/15'));

        self::assertSame('post:15', $result);
    }

    public function test_dispatcher_dispatches_closure_and_injects_request_and_route(): void
    {
        $app = $this->createApplication();
        $route = new Route(['GET'], '/users/{id}', function (Request $request, Route $route, string $id): string {
            return $request->method() . ':' . $route->uri() . ':' . $id;
        });
        $resolved = new ResolvedRoute($route, ['id' => '3']);
        $request = Request::create('GET', '/users/3')
            ->withAttribute('route', $route)
            ->withAttribute('route_parameters', ['id' => '3'])
            ->withAttribute('id', '3');

        $result = $app->controllers()->dispatchResolvedRoute($resolved, $request);

        self::assertSame('GET:/users/{id}:3', $result);
    }

    public function test_application_exposes_controller_dispatcher(): void
    {
        $app = $this->createApplication();

        self::assertInstanceOf(ControllerDispatcher::class, $app->controllers());
        self::assertSame($app->controllers(), $app->container()->make(ControllerDispatcher::class));
    }
}

final class DispatcherController
{
    public function show(string $id): string
    {
        return 'post:' . $id;
    }
}
