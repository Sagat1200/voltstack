<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Routing;

use Quantum\Http\Request;
use Quantum\Routing\Router;
use VoltStack\Framework\Tests\TestCase;

final class RouterTest extends TestCase
{
    public function test_router_resolves_route_with_path_parameters(): void
    {
        $router = new Router();
        $router->get('/users/{id}', static fn () => 'ok');

        $resolved = $router->resolve(Request::create('GET', '/users/42'));

        self::assertNotNull($resolved);
        self::assertSame('42', $resolved->parameters()['id']);
        self::assertSame('/users/{id}', $resolved->route()->uri());
    }

    public function test_router_respects_http_method(): void
    {
        $router = new Router();
        $router->post('/users', static fn () => 'created');

        $resolved = $router->resolve(Request::create('GET', '/users'));

        self::assertNull($resolved);
    }

    public function test_router_applies_group_prefixes(): void
    {
        $router = new Router();

        $router->group(['prefix' => 'api'], function (Router $router): void {
            $router->group(['prefix' => 'v1'], function (Router $router): void {
                $router->get('/users/{id}', static fn () => 'ok');
            });
        });

        $resolved = $router->resolve(Request::create('GET', '/api/v1/users/42'));

        self::assertNotNull($resolved);
        self::assertSame('/api/v1/users/{id}', $resolved->route()->uri());
        self::assertSame('42', $resolved->parameters()['id']);
    }

    public function test_router_supports_chainable_group_registrar(): void
    {
        $router = new Router();

        $router
            ->prefix('admin')
            ->prefix('users')
            ->group(function (Router $router): void {
                $router->get('/{id}', static fn () => 'ok');
            });

        $resolved = $router->resolve(Request::create('GET', '/admin/users/10'));

        self::assertNotNull($resolved);
        self::assertSame('/admin/users/{id}', $resolved->route()->uri());
        self::assertSame('10', $resolved->parameters()['id']);
    }

    public function test_router_generates_url_for_named_route(): void
    {
        $router = new Router();
        $router->get('/users/{id}', static fn () => 'ok')->name('users.show');

        $url = $router->route('users.show', ['id' => 42], ['tab' => 'profile']);

        self::assertSame('/users/42?tab=profile', $url);
    }

    public function test_router_applies_group_name_prefixes_to_routes(): void
    {
        $router = new Router();

        $router
            ->prefix('api')
            ->name('api')
            ->group(function (Router $router): void {
                $router
                    ->prefix('v1')
                    ->name('v1')
                    ->group(function (Router $router): void {
                        $router->get('/users/{id}', static fn () => 'ok')->name('show');
                    });
            });

        $url = $router->route('api.v1.show', ['id' => 5]);

        self::assertSame('/api/v1/users/5', $url);
    }

    public function test_router_throws_for_unknown_named_route(): void
    {
        $router = new Router();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Route [missing.route] is not defined.');

        $router->route('missing.route');
    }
}
