<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\HttpKernel;

use Closure;
use Quantum\Exceptions\HttpException;
use Quantum\Http\FormRequest;
use Quantum\Http\Request;
use Quantum\Http\Response;
use Quantum\HttpKernel\Contracts\MiddlewareInterface;
use VoltStack\Framework\Tests\TestCase;

final class HttpKernelTest extends TestCase
{
    public function test_kernel_dispatches_closure_route_and_injects_request(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/hello/{name}', function (Request $request, string $name): string {
            return 'Hello ' . $name . ' via ' . $request->method();
        });

        $response = $app->kernel()->handle(Request::create('GET', '/hello/VoltStack'));

        self::assertSame(200, $response->status());
        self::assertSame('Hello VoltStack via GET', $response->content());
    }

    public function test_kernel_dispatches_controller_string_through_container(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/controller/{id}', ControllerHandler::class . '@show');

        $response = $app->kernel()->handle(Request::create('GET', '/controller/7'));

        self::assertSame(200, $response->status());
        self::assertSame('controller:7', $response->content());
    }

    public function test_kernel_runs_middleware_stack(): void
    {
        $app = $this->createApplication();
        $app->container()->instance(UppercaseMiddleware::class, new UppercaseMiddleware());

        $app->router()
            ->get('/middleware', static fn (): string => 'ok')
            ->middleware(UppercaseMiddleware::class);

        $response = $app->kernel()->handle(Request::create('GET', '/middleware'));

        self::assertSame('OK', $response->content());
    }

    public function test_kernel_returns_not_found_response_for_unknown_route(): void
    {
        $app = $this->createApplication();

        $response = $app->kernel()->handle(Request::create('GET', '/missing'));

        self::assertSame(404, $response->status());
        self::assertSame('Not Found', $response->content());
    }

    public function test_kernel_returns_not_found_when_route_binding_cannot_be_resolved(): void
    {
        $app = $this->createApplication();
        $app->bindRouteType(BoundPost::class, static fn (string $value): ?BoundPost => null);
        $app->router()->get('/posts/{post}', BoundPostController::class . '@show');

        $response = $app->kernel()->handle(Request::create('GET', '/posts/999'));

        self::assertSame(404, $response->status());
        self::assertSame('Route binding [post] could not be resolved.', $response->content());
    }

    public function test_kernel_returns_validation_error_for_invalid_form_request(): void
    {
        $app = $this->createApplication();
        $app->router()->post('/users', CreateUserController::class . '@store');

        $response = $app->kernel()->handle(
            Request::create('POST', '/users', [], [
                'email' => 'bad-email',
                'name' => 'ab',
            ], ['Accept' => 'application/json'])
        );

        self::assertSame(422, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame(
            '{"message":"The given data was invalid.","status":422,"errors":{"email":["The email field must be a valid email address."],"name":["The name field must be at least 3."]}}',
            $response->content()
        );
    }

    public function test_kernel_returns_forbidden_for_unauthorized_form_request(): void
    {
        $app = $this->createApplication();
        $app->router()->post('/admin', CreateAdminController::class . '@store');

        $response = $app->kernel()->handle(
            Request::create('POST', '/admin', [], [
                'email' => 'admin@example.com',
            ])
        );

        self::assertSame(403, $response->status());
        self::assertSame('This request is unauthorized.', $response->content());
    }

    public function test_kernel_uses_custom_failed_authorization_hook(): void
    {
        $app = $this->createApplication();
        $app->router()->post('/admin/custom', CreateCustomAdminController::class . '@store');

        $response = $app->kernel()->handle(
            Request::create('POST', '/admin/custom', [], [
                'email' => 'admin@example.com',
            ])
        );

        self::assertSame(418, $response->status());
        self::assertSame('Custom authorization failed.', $response->content());
    }

    public function test_kernel_uses_custom_failed_validation_hook(): void
    {
        $app = $this->createApplication();
        $app->router()->post('/users/custom', CreateCustomUserController::class . '@store');

        $response = $app->kernel()->handle(
            Request::create('POST', '/users/custom', [], [
                'email' => 'bad-email',
            ])
        );

        self::assertSame(409, $response->status());
        self::assertSame('Custom validation failed.', $response->content());
    }

    public function test_kernel_uses_form_request_messages_and_attributes(): void
    {
        $app = $this->createApplication();
        $app->router()->post('/users/expressive', CreateExpressiveUserController::class . '@store');

        $response = $app->kernel()->handle(
            Request::create('POST', '/users/expressive', [], [
                'email' => 'bad-email',
            ], ['Accept' => 'application/json'])
        );

        self::assertSame(422, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame(
            '{"message":"The given data was invalid.","status":422,"errors":{"email":["Debes indicar un correo electronico valido."],"name":["El campo nombre completo es obligatorio."]}}',
            $response->content()
        );
    }
}

final class ControllerHandler
{
    public function show(string $id): string
    {
        return 'controller:' . $id;
    }
}

final class UppercaseMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return Response::make(strtoupper($response->content()), $response->status(), $response->headers());
    }
}

final class BoundPostController
{
    public function show(BoundPost $post): string
    {
        return $post->id;
    }
}

final class BoundPost
{
    public function __construct(
        public string $id,
    ) {
    }
}

final class CreateUserController
{
    public function store(CreateUserFormRequest $request): string
    {
        return $request->validated('email');
    }
}

final class CreateAdminController
{
    public function store(CreateAdminFormRequest $request): string
    {
        return $request->validated('email');
    }
}

final class CreateCustomAdminController
{
    public function store(CreateCustomAdminFormRequest $request): string
    {
        return $request->validated('email');
    }
}

final class CreateCustomUserController
{
    public function store(CreateCustomUserFormRequest $request): string
    {
        return $request->validated('email');
    }
}

final class CreateExpressiveUserController
{
    public function store(CreateExpressiveUserFormRequest $request): string
    {
        return $request->validated('email');
    }
}

final class CreateUserFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:3'],
        ];
    }
}

final class CreateAdminFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}

final class CreateCustomAdminFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    protected function failedAuthorization(): never
    {
        throw new HttpException(418, 'Custom authorization failed.');
    }
}

final class CreateCustomUserFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    protected function failedValidation(\Quantum\Validation\ValidationException $exception): never
    {
        throw new HttpException(409, 'Custom validation failed.');
    }
}

final class CreateExpressiveUserFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Debes indicar un :attribute valido.',
            'required' => 'El campo :attribute es obligatorio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'correo electronico',
            'name' => 'nombre completo',
        ];
    }
}
