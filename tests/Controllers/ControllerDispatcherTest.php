<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Controllers;

use Quantum\Controllers\ControllerDispatcher;
use Quantum\Http\FormRequest;
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

    public function test_dispatcher_resolves_parameter_binding_before_invoking_handler(): void
    {
        $app = $this->createApplication();
        $app->bindRouteParameter('user', static fn (string $value): BoundUser => new BoundUser($value, 'parameter'));

        $route = new Route(['GET'], '/users/{user}', BoundUserController::class . '@show');
        $resolved = new ResolvedRoute($route, ['user' => '24']);
        $request = Request::create('GET', '/users/24')->withAttribute('route', $route);

        $result = $app->controllers()->dispatchResolvedRoute($resolved, $request);

        self::assertSame('parameter:24', $result);
    }

    public function test_dispatcher_resolves_type_binding_when_parameter_name_binding_is_missing(): void
    {
        $app = $this->createApplication();
        $app->bindRouteType(BoundUser::class, static fn (string $value): BoundUser => new BoundUser($value, 'type'));

        $route = new Route(['GET'], '/members/{member}', TypeBoundUserController::class . '@show');
        $resolved = new ResolvedRoute($route, ['member' => '35']);
        $request = Request::create('GET', '/members/35')->withAttribute('route', $route);

        $result = $app->controllers()->dispatchResolvedRoute($resolved, $request);

        self::assertSame('type:35', $result);
    }

    public function test_dispatcher_injects_form_request_with_validated_data(): void
    {
        $app = $this->createApplication();
        $route = new Route(['POST'], '/teams/{team}', FormRequestController::class . '@store');
        $resolved = new ResolvedRoute($route, ['team' => 'core']);
        $request = Request::create('POST', '/teams/core', [], [
            'email' => 'user@example.com',
            'name' => 'VoltStack',
        ])
            ->withAttribute('route', $route)
            ->withAttribute('route_parameters', ['team' => 'core'])
            ->withAttribute('team', 'core');

        $result = $app->controllers()->dispatchResolvedRoute($resolved, $request);

        self::assertSame([
            'email' => 'user@example.com',
            'name' => 'VoltStack',
            'team' => 'core',
        ], $result);
    }

    public function test_form_request_runs_prepare_and_passed_validation_hooks(): void
    {
        HookedStoreUserRequest::$prepared = false;
        HookedStoreUserRequest::$passed = false;

        $app = $this->createApplication();
        $route = new Route(['POST'], '/hooked', HookedFormRequestController::class . '@store');
        $resolved = new ResolvedRoute($route, []);
        $request = Request::create('POST', '/hooked', [], [
            'email' => 'USER@EXAMPLE.COM',
            'name' => 'Vo',
        ])->withAttribute('route', $route);

        $result = $app->controllers()->dispatchResolvedRoute($resolved, $request);

        self::assertTrue(HookedStoreUserRequest::$prepared);
        self::assertTrue(HookedStoreUserRequest::$passed);
        self::assertSame([
            'email' => 'user@example.com',
            'name' => 'VoltStack',
        ], $result);
    }

    public function test_form_request_exposes_safe_only_and_except_helpers(): void
    {
        $app = $this->createApplication();
        $route = new Route(['POST'], '/teams/{team}/safe', SafeFormRequestController::class . '@store');
        $resolved = new ResolvedRoute($route, ['team' => 'core']);
        $request = Request::create('POST', '/teams/core/safe', [], [
            'email' => 'user@example.com',
            'name' => 'VoltStack',
        ])
            ->withAttribute('route', $route)
            ->withAttribute('route_parameters', ['team' => 'core'])
            ->withAttribute('team', 'core');

        $result = $app->controllers()->dispatchResolvedRoute($resolved, $request);

        self::assertSame([
            'safe' => [
                'team' => 'core',
                'email' => 'user@example.com',
                'name' => 'VoltStack',
            ],
            'only' => [
                'email' => 'user@example.com',
                'team' => 'core',
            ],
            'except' => [
                'team' => 'core',
                'email' => 'user@example.com',
            ],
        ], $result);
    }

    public function test_form_request_validated_supports_dot_notation(): void
    {
        $app = $this->createApplication();
        $route = new Route(['POST'], '/profiles/nested', NestedFormRequestController::class . '@store');
        $resolved = new ResolvedRoute($route, []);
        $request = Request::create('POST', '/profiles/nested', [], [
            'profile' => [
                'name' => 'VoltStack',
            ],
            'items' => [
                ['name' => 'core_api'],
                ['name' => 'admin-ui'],
            ],
        ])->withAttribute('route', $route);

        $result = $app->controllers()->dispatchResolvedRoute($resolved, $request);

        self::assertSame([
            'profile_name' => 'VoltStack',
            'first_item_name' => 'core_api',
            'missing' => 'fallback',
        ], $result);
    }

    public function test_form_request_only_and_except_support_dot_notation(): void
    {
        $app = $this->createApplication();
        $route = new Route(['POST'], '/profiles/nested/subset', NestedSubsetFormRequestController::class . '@store');
        $resolved = new ResolvedRoute($route, []);
        $request = Request::create('POST', '/profiles/nested/subset', [], [
            'profile' => [
                'name' => 'VoltStack',
            ],
            'items' => [
                ['name' => 'core_api'],
                ['name' => 'admin-ui'],
            ],
        ])->withAttribute('route', $route);

        $result = $app->controllers()->dispatchResolvedRoute($resolved, $request);

        self::assertSame([
            'only' => [
                'profile' => [
                    'name' => 'VoltStack',
                ],
                'items' => [
                    [
                        'name' => 'core_api',
                    ],
                ],
            ],
            'except' => [
                'items' => [
                    [
                        'name' => 'core_api',
                    ],
                ],
            ],
        ], $result);
    }
}

final class DispatcherController
{
    public function show(string $id): string
    {
        return 'post:' . $id;
    }
}

final class BoundUserController
{
    public function show(BoundUser $user): string
    {
        return $user->source . ':' . $user->id;
    }
}

final class TypeBoundUserController
{
    public function show(BoundUser $member): string
    {
        return $member->source . ':' . $member->id;
    }
}

final class BoundUser
{
    public function __construct(
        public string $id,
        public string $source,
    ) {
    }
}

final class FormRequestController
{
    public function store(StoreUserRequest $request): array
    {
        return [
            'email' => $request->validated('email'),
            'name' => $request->validated('name'),
            'team' => $request->routeParameter('team'),
        ];
    }
}

final class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:3'],
        ];
    }
}

final class HookedFormRequestController
{
    public function store(HookedStoreUserRequest $request): array
    {
        return $request->validated();
    }
}

final class HookedStoreUserRequest extends FormRequest
{
    public static bool $prepared = false;
    public static bool $passed = false;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:3'],
        ];
    }

    protected function prepareForValidation(): void
    {
        self::$prepared = true;

        $this->merge([
            'email' => strtolower((string) $this->input('email', '')),
            'name' => 'VoltStack',
        ]);
    }

    protected function passedValidation(): void
    {
        self::$passed = true;
    }
}

final class SafeFormRequestController
{
    public function store(SafeStoreUserRequest $request): array
    {
        return [
            'safe' => $request->safe(),
            'only' => $request->only(['email', 'team']),
            'except' => $request->except('name'),
        ];
    }
}

final class SafeStoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:3'],
            'team' => ['required', 'string'],
        ];
    }
}

final class NestedFormRequestController
{
    public function store(NestedStoreRequest $request): array
    {
        return [
            'profile_name' => $request->validated('profile.name'),
            'first_item_name' => $request->validated('items.0.name'),
            'missing' => $request->validated('items.5.name', 'fallback'),
        ];
    }
}

final class NestedSubsetFormRequestController
{
    public function store(NestedStoreRequest $request): array
    {
        return [
            'only' => $request->only(['profile.name', 'items.0.name']),
            'except' => $request->except(['profile.name', 'items.1.name']),
        ];
    }
}

final class NestedStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'profile.name' => ['required', 'string'],
            'items.*.name' => ['required', 'alpha_dash'],
        ];
    }
}
