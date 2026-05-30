<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Application;

use Quantum\Bootstrap\ServiceProvider;
use Quantum\Config\ConfigRepository;
use Quantum\Controllers\ControllerDispatcher;
use VoltStack\Framework\Tests\TestCase;
use VoltStack\Platform\Application;

final class ApplicationTest extends TestCase
{
    public function test_application_boots_and_loads_config(): void
    {
        $app = $this->createApplication();
        $app->boot();

        self::assertTrue($app->isBooted());
        self::assertSame('VoltStack Test', $app->config()->get('app.name'));
        self::assertInstanceOf(ConfigRepository::class, $app->config());
    }

    public function test_application_boot_is_idempotent(): void
    {
        BootFlagProvider::$registerCalls = 0;
        BootFlagProvider::$bootCalls = 0;

        $app = $this->createApplication([], [
            BootFlagProvider::class,
        ]);

        $app->boot();
        $app->boot();

        self::assertSame(1, BootFlagProvider::$registerCalls);
        self::assertSame(1, BootFlagProvider::$bootCalls);
    }

    public function test_application_exposes_container_and_paths(): void
    {
        $app = $this->createApplication();

        self::assertInstanceOf(Application::class, $app->container()->make(Application::class));
        self::assertStringEndsWith('config', $app->configPath());
        self::assertStringEndsWith('bootstrap', $app->bootstrapPath());
        self::assertSame($app->basePath('bootstrap'), $app->basePath() . DIRECTORY_SEPARATOR . 'bootstrap');
    }

    public function test_application_generates_named_route_urls(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/posts/{slug}', static fn () => 'ok')->name('posts.show');

        self::assertSame('/posts/hello-world', $app->route('posts.show', ['slug' => 'hello-world']));
        self::assertSame('/posts/hello-world?page=2', route('posts.show', ['slug' => 'hello-world'], ['page' => 2]));
    }

    public function test_application_exposes_controller_dispatcher(): void
    {
        $app = $this->createApplication();

        self::assertInstanceOf(ControllerDispatcher::class, $app->controllers());
    }
}

final class BootFlagProvider extends ServiceProvider
{
    public static int $registerCalls = 0;
    public static int $bootCalls = 0;

    public function register(): void
    {
        self::$registerCalls++;
    }

    public function boot(): void
    {
        self::$bootCalls++;
    }
}
