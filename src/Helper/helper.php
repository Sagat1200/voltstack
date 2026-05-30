<?php

declare(strict_types=1);

use VoltStack\Facades\Facade;
use VoltStack\Platform\Application;

if (!function_exists('voltstack_set_application')) {
    function voltstack_set_application(Application $application): void
    {
        Facade::setApplication($application);
        app($application);
    }
}

if (!function_exists('app')) {
    function app(string|Application|null $abstract = null): mixed
    {
        static $application = null;

        if ($abstract instanceof Application) {
            $application = $abstract;
            return $application;
        }

        if (!$application instanceof Application) {
            throw new \RuntimeException('Application has not been initialized.');
        }

        return $abstract === null ? $application : $application->container()->make($abstract);
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        $repository = app()->config();

        if ($key === null) {
            return $repository;
        }

        return $repository->get($key, $default);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false ? $default : ($value ?? $default);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $parameters = [], array $query = []): string
    {
        return app()->route($name, $parameters, $query);
    }
}
