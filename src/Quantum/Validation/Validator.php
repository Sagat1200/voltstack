<?php

declare(strict_types=1);

namespace Quantum\Validation;

use Quantum\Validation\Contracts\ValidatorInterface;

final class Validator implements ValidatorInterface
{
    public function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = $this->normalizeRules($fieldRules);
            $value = $data[$field] ?? null;
            $present = array_key_exists($field, $data);

            foreach ($fieldRules as $rule) {
                [$name, $argument] = $this->parseRule($rule);
                $ruleName = $this->normalizeRuleName($name);

                if ($name === 'required' && (!$present || $value === null || $value === '' || $value === [])) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if (!$present || $value === null) {
                    continue;
                }

                if ($name === 'string' && !is_string($value)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if (($name === 'int' || $name === 'integer') && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                    );
                    continue;
                }

                if ($name === 'min' && $argument !== null && !$this->passesMinRule($value, (int) $argument)) {
                    $errors[$field][] = $this->messageFor(
                        $field,
                        $name,
                        $ruleName,
                        $messages,
                        $attributes,
                        ['min' => (string) (int) $argument],
                    );
                }
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    protected function normalizeRules(array|string $rules): array
    {
        if (is_string($rules)) {
            return array_values(array_filter(explode('|', $rules), static fn(string $rule): bool => $rule !== ''));
        }

        return $rules;
    }

    protected function parseRule(string $rule): array
    {
        if (!str_contains($rule, ':')) {
            return [$rule, null];
        }

        [$name, $argument] = explode(':', $rule, 2);

        return [$name, $argument];
    }

    protected function normalizeRuleName(string $rule): string
    {
        return $rule === 'int' ? 'integer' : $rule;
    }

    protected function messageFor(
        string $field,
        string $rule,
        string $normalizedRule,
        array $messages,
        array $attributes,
        array $replacements = []
    ): string {
        $template = $this->findMessageTemplate($field, $rule, $normalizedRule, $messages)
            ?? $this->defaultMessageTemplate($normalizedRule);

        $replacements = array_replace([
            'attribute' => $attributes[$field] ?? $field,
        ], $replacements);

        foreach ($replacements as $key => $value) {
            $template = str_replace(':' . $key, (string) $value, $template);
        }

        return $template;
    }

    protected function findMessageTemplate(
        string $field,
        string $rule,
        string $normalizedRule,
        array $messages
    ): ?string {
        $keys = [
            $field . '.' . $rule,
            $field . '.' . $normalizedRule,
            $rule,
            $normalizedRule,
        ];

        foreach (array_values(array_unique($keys)) as $key) {
            if (array_key_exists($key, $messages)) {
                return $messages[$key];
            }
        }

        return null;
    }

    protected function defaultMessageTemplate(string $rule): string
    {
        return match ($rule) {
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute field must be a string.',
            'integer' => 'The :attribute field must be an integer.',
            'email' => 'The :attribute field must be a valid email address.',
            'min' => 'The :attribute field must be at least :min.',
            default => 'The :attribute field is invalid.',
        };
    }

    protected function passesMinRule(mixed $value, int $minimum): bool
    {
        if (is_numeric($value)) {
            return (float) $value >= $minimum;
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $minimum;
        }

        if (is_array($value)) {
            return count($value) >= $minimum;
        }

        return false;
    }
}
