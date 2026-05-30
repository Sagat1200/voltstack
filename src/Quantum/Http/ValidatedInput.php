<?php

declare(strict_types=1);

namespace Quantum\Http;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use Traversable;

final class ValidatedInput implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    public function __construct(
        protected array $data,
    ) {}

    public function all(): array
    {
        return $this->data;
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->all();
        }

        return $this->getArrayValue($this->data, $key, $default);
    }

    public function only(array|string $keys): array
    {
        $keys = $this->normalizeKeys($keys);
        $result = [];

        foreach ($keys as $key) {
            if (!$this->hasArrayValue($this->data, $key)) {
                continue;
            }

            if (array_key_exists($key, $this->data)) {
                $result[$key] = $this->data[$key];
                continue;
            }

            $this->setArrayValue($result, $key, $this->getArrayValue($this->data, $key));
        }

        return $result;
    }

    public function except(array|string $keys): array
    {
        $keys = $this->normalizeKeys($keys);
        $result = $this->data;

        foreach ($keys as $key) {
            $this->forgetArrayValue($result, $key);
        }

        return $result;
    }

    public function has(array|string $keys): bool
    {
        foreach ($this->normalizeKeys($keys) as $key) {
            if (!$this->hasArrayValue($this->data, $key)) {
                return false;
            }
        }

        return true;
    }

    public function missing(array|string $keys): bool
    {
        return !$this->has($keys);
    }

    public function filled(array|string $keys): bool
    {
        foreach ($this->normalizeKeys($keys) as $key) {
            if (!$this->has($key) || !$this->isFilledValue($this->get($key))) {
                return false;
            }
        }

        return true;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!is_string($offset)) {
            return null;
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('ValidatedInput is read-only.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('ValidatedInput is read-only.');
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function jsonSerialize(): array
    {
        return $this->all();
    }

    protected function normalizeKeys(array|string $keys): array
    {
        return is_array($keys) ? $keys : [$keys];
    }

    protected function isFilledValue(mixed $value): bool
    {
        return !($value === null || $value === '' || $value === []);
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