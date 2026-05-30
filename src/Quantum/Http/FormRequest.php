<?php

declare(strict_types=1);

namespace Quantum\Http;

use Quantum\Exceptions\HttpException;
use Quantum\Routing\Route;
use Quantum\Validation\Contracts\ValidatorInterface;
use Quantum\Validation\ValidationException;

abstract class FormRequest
{
    protected array $data = [];

    protected array $validated = [];

    final public function __construct(
        protected Request $request,
        protected ValidatorInterface $validator,
    ) {
        $this->data = $this->defaultData();
    }

    public static function from(Request $request, ValidatorInterface $validator): static
    {
        $instance = new static($request, $validator);
        $instance->validateResolved();

        return $instance;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }

    public function attributes(): array
    {
        return [];
    }

    public function validated(?string $key = null, mixed $default = null): mixed
    {
        return $this->validatedData()->get($key, $default);
    }

    public function validatedData(): ValidatedInput
    {
        return new ValidatedInput($this->validated);
    }

    public function safe(): ValidatedInput
    {
        return $this->validatedData();
    }

    public function only(array|string $keys): array
    {
        return $this->safe()->only($keys);
    }

    public function except(array|string $keys): array
    {
        return $this->safe()->except($keys);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $this->request->query($key, $default);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->request->header($key, $default);
    }

    public function method(): string
    {
        return $this->request->method();
    }

    public function uri(): string
    {
        return $this->request->uri();
    }

    public function path(): string
    {
        return $this->request->path();
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->request->attribute($key, $default);
    }

    public function route(): ?Route
    {
        $route = $this->request->attribute('route');

        return $route instanceof Route ? $route : null;
    }

    public function routeParameter(string $key, mixed $default = null): mixed
    {
        return $this->routeParameters()[$key] ?? $default;
    }

    public function request(): Request
    {
        return $this->request;
    }

    protected function validationData(): array
    {
        return $this->all();
    }

    protected function prepareForValidation(): void {}

    protected function passedValidation(): void {}

    protected function failedAuthorization(): never
    {
        throw new HttpException(403, 'This request is unauthorized.');
    }

    protected function failedValidation(ValidationException $exception): never
    {
        throw $exception;
    }

    protected function merge(array $data): void
    {
        $this->data = array_replace($this->data, $data);
    }

    protected function replace(array $data): void
    {
        $this->data = $data;
    }

    protected function validateResolved(): void
    {
        $this->prepareForValidation();

        if ($this->authorize() === false) {
            $this->failedAuthorization();
        }

        $rules = $this->rules();
        $data = $this->validationData();

        try {
            $this->validated = $rules === []
                ? $data
                : $this->validator->validate(
                    $data,
                    $rules,
                    $this->messages(),
                    $this->attributes(),
                );
        } catch (ValidationException $exception) {
            $this->failedValidation($exception);
        }

        $this->passedValidation();
    }

    protected function routeParameters(): array
    {
        $parameters = $this->request->attribute('route_parameters', []);

        return is_array($parameters) ? $parameters : [];
    }

    protected function defaultData(): array
    {
        return array_replace(
            $this->routeParameters(),
            $this->query(),
            $this->input(),
        );
    }
}