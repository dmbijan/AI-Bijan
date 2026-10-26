<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Observability (BAB 61) — health, readiness, metrik. */
class HealthTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_health_memulangkan_ok(): void
    {
        $r = $this->getJson('/health')->assertOk()->json();

        $this->assertSame('ok', $r['status']);
        $this->assertArrayHasKey('time', $r);
    }

    public function test_ready_semua_semakan_lulus(): void
    {
        $r = $this->getJson('/ready')->assertOk()->json();

        $this->assertSame('ready', $r['status']);
        $this->assertSame([], $r['failed']);
        $this->assertTrue($r['checks']['database']['ok']);
        $this->assertTrue($r['checks']['cache']['ok']);
        $this->assertTrue($r['checks']['storage']['ok']);
        $this->assertTrue($r['checks']['migrations']['ok']);
    }

    public function test_ready_tidak_dedahkan_kredensial(): void
    {
        $body = strtolower($this->getJson('/ready')->getContent());
        $rahsia = (string) config('database.connections.'.config('database.default').'.password');

        $this->assertStringNotContainsString('password', $body);
        $this->assertStringNotContainsString('secret', $body);
        $this->assertStringNotContainsString('token', $body);

        if ($rahsia !== '') {
            $this->assertStringNotContainsString(strtolower($rahsia), $body);
        }
    }

    public function test_metrik_ringkasan(): void
    {
        $r = $this->getJson('/metrics')->assertOk()->json();

        $this->assertArrayHasKey('tenants', $r);
        $this->assertArrayHasKey('queue', $r);
        $this->assertArrayHasKey('php', $r);
        $this->assertArrayHasKey('laravel', $r);
        $this->assertIsNumeric($r['db_latency_ms']);
    }

    public function test_activity_log_tidak_didedahkan_dalam_health(): void
    {
        $body = $this->getJson('/ready')->getContent();

        $this->assertStringNotContainsString('activity_log', $body);
        $this->assertStringNotContainsString('api_key', strtolower($body));
    }
}
