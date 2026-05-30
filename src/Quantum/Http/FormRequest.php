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
        if ($key === null) {
            return $this->validated;
        }

        return $this->getArrayValue($this->validated, $key, $default);
    }

    public function safe(): array
    {
        return $this->validated();
    }

    public function only(array|string $keys): array
    {
        $keys = $this->normalizeKeys($keys);
        $result = [];

        foreach ($keys as $key) {
            if (!$this->hasArrayValue($this->safe(), $key)) {
                continue;
            }

            if (array_key_exists($key, $this->safe())) {
                $result[$key] = $this->safe()[$key];
                continue;
            }

            $this->setArrayValue($result, $key, $this->getArrayValue($this->safe(), $key));
        }

        return $result;
    }

    public function except(array|string $keys): array
    {
        $keys = $this->normalizeKeys($keys);
        $result = $this->safe();

        foreach ($keys as $key) {
            $this->forgetArrayValue($result, $key);
        }

        return $result;
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

    protected function normalizeKeys(array|string $keys): array
    {
        return is_array($keys) ? $keys : [$keys];
    }

    protected function getArrayValue(array $data, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        $segments = explode('.', $key);
        $current = $data;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    protected function hasArrayValue(array $data, string $key): bool
    {
        if (array_key_exists($key, $data)) {
            return true;
        }

        $segments = explode('.', $key);
        $current = $data;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        return true;
    }

    protected function setArrayValue(array &$data, string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $current = &$data;

        foreach ($segments as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current = $value;
    }

    protected function forgetArrayValue(array &$data, string $key): void
    {
        if (array_key_exists($key, $data)) {
            unset($data[$key]);

            return;
        }

        $this->forgetArrayValueRecursive($data, explode('.', $key));
    }

    protected function forgetArrayValueRecursive(array &$data, array $segments): bool
    {
        $segment = array_shift($segments);

        if ($segment === null || !array_key_exists($segment, $data)) {
            return false;
        }

        if ($segments === []) {
            unset($data[$segment]);

            return $data === [];
        }

        if (!is_array($data[$segment])) {
            return false;
        }

        $shouldForgetChild = $this->forgetArrayValueRecursive($data[$segment], $segments);

        if ($shouldForgetChild) {
            unset($data[$segment]);
        }

        return $data === [];
    }
}