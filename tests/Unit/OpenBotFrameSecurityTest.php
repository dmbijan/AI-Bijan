<?php

namespace Tests\Unit;

use App\Http\Middleware\SecureHeaders;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Tests\TestCase;

class OpenBotFrameSecurityTest extends TestCase
{
    public function test_configured_openbot_origin_is_allowed_as_a_frame_source(): void
    {
        config(['openbot.url' => 'https://bots.example.test:3001/bot']);

        $response = app(SecureHeaders::class)->handle(
            Request::create('/app/openbot'),
            fn () => response('<html></html>')->header('Content-Type', 'text/html')
        );

        $this->assertStringContainsString(
            "frame-src 'self' https://bots.example.test:3001",
            $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_unconfigured_openbot_keeps_the_default_frame_source_policy(): void
    {
        config(['openbot.url' => null]);

        $response = app(SecureHeaders::class)->handle(
            Request::create('/app/openbot'),
            fn () => response('<html></html>')->header('Content-Type', 'text/html')
        );

        $this->assertStringContainsString(
            "frame-src 'self';",
            $response->headers->get('Content-Security-Policy')
        );
        $this->assertStringNotContainsString(
            "frame-src 'self' https://",
            $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_configured_application_origin_is_recognized_as_stateful(): void
    {
        config(['sanctum.stateful' => ['127.0.0.1:8145']]);
        $request = Request::create(
            '/api/v1/auth/login',
            'POST',
            [],
            [],
            [],
            ['HTTP_ORIGIN' => 'http://127.0.0.1:8145']
        );

        $this->assertTrue(EnsureFrontendRequestsAreStateful::fromFrontend($request));
    }
}
