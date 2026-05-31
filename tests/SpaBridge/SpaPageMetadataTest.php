<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\SpaBridge;

use InvalidArgumentException;
use Quantum\Http\Request;
use VoltStack\Framework\Tests\TestCase;

final class SpaPageMetadataTest extends TestCase
{
    public function test_spa_page_includes_navigation_metadata_for_named_routes(): void
    {
        $app = $this->createApplication();
        $app->router()
            ->get('/dashboard', fn() => $app->spa()->page('Dashboard\\Home', [], [
                'title' => 'Dashboard',
            ]))
            ->name('dashboard.home');

        $response = $app->kernel()->handle(Request::create('GET', '/dashboard'));
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Dashboard/Home', $data['component']);
        self::assertSame('Dashboard', $data['meta']['title']);
        self::assertSame('/dashboard', $data['meta']['navigation']['url']);
        self::assertSame('/dashboard', $data['meta']['navigation']['path']);
        self::assertSame('dashboard.home', $data['meta']['navigation']['route']);
    }

    public function test_spa_page_rejects_invalid_component_names(): void
    {
        $app = $this->createApplication();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SPA component name');

        $app->spa()->page('Dashboard Home');
    }
}
