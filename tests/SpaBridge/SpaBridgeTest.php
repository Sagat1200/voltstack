<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\SpaBridge;

use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;
use Quantum\SpaBridge\Payloads\SpaPagePayload;
use Quantum\SpaBridge\SpaBridge;
use Quantum\SpaBridge\SpaResponder;
use VoltStack\Framework\Tests\TestCase;

final class SpaBridgeTest extends TestCase
{
    public function test_page_payload_matches_universal_spa_shape(): void
    {
        $payload = new SpaPagePayload('Dashboard/Home', [
            'stats' => ['users' => 10],
        ], [
            'layout' => 'main',
        ]);

        $data = $payload->toArray();

        self::assertSame('spa.page', $data['type']);
        self::assertSame('1.0.0', $data['version']);
        self::assertSame(200, $data['status']);
        self::assertTrue($data['success']);
        self::assertSame('Dashboard/Home', $data['component']);
        self::assertSame(['stats' => ['users' => 10]], $data['props']);
        self::assertSame(['layout' => 'main'], $data['meta']);
        self::assertSame([], $data['errors']);
        self::assertNull($data['redirect']);
        self::assertSame([], $data['context']);
        self::assertIsString($data['request_id']);
        self::assertStringStartsWith('req_', $data['request_id']);
        self::assertIsInt($data['timestamp']);
    }

    public function test_spa_responder_returns_validation_payload_as_json_response(): void
    {
        $responder = new SpaResponder(new ResponseFactory());
        $response = $responder->validation([
            'email' => ['The email field is required.'],
        ]);
        $data = json_decode($response->content(), true, 512, JSON_THROW_ON_ERROR);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(422, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('spa.validation', $data['type']);
        self::assertSame('1.0.0', $data['version']);
        self::assertSame(422, $data['status']);
        self::assertFalse($data['success']);
        self::assertSame(['email' => ['The email field is required.']], $data['errors']);
        self::assertSame('null', $data['meta']['frontend']['adapter']['name']);
        self::assertSame('0.0.0', $data['meta']['frontend']['adapter']['version']);
        self::assertSame([], $data['meta']['frontend']['entrypoints']);
        self::assertSame([], $data['props']);
        self::assertNull($data['component']);
        self::assertNull($data['redirect']);
        self::assertIsString($data['request_id']);
        self::assertIsInt($data['timestamp']);
    }

    public function test_spa_bridge_exposes_page_objects_and_can_render_them_through_the_responder(): void
    {
        $bridge = new SpaBridge(new SpaResponder(new ResponseFactory()));
        $page = $bridge->page('Users/Index', ['users' => [['id' => 1]]], ['title' => 'Users']);
        $response = $bridge->payload($page->toPayload());

        self::assertSame('Users/Index', $page->component());
        self::assertSame('Users', $page->meta()['title']);
        self::assertSame(200, $response->status());
        self::assertStringContainsString('"type":"spa.page"', $response->content());
        self::assertStringContainsString('"component":"Users\/Index"', $response->content());
    }
}
