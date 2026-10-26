<?php

namespace Tests\Feature;

use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunitySpace;
use App\Modules\Community\Services\CommunityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Pengasingan tenant — modul COMMUNITY.
 *
 * Ditambah 27 Okt 2026 (P1-1a) selepas audit mendapati `CommunitySpace`,
 * `CommunityPost` dan `CommunityReply` mempunyai kolum `tenant_id` tetapi
 * TIDAK guna trait `BelongsToTenant` → tiada skop global.
 *
 * ⚠️ Jika ujian di bawah GAGAL pada commit sebelum pembaikan, itu membuktikan
 * IDOR sebenar melalui URL (`GET /api/v1/community/posts/{id}`), bukan sekadar
 * teori.
 */
class CommunityTenantIsolationTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private CommunityService $s;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@community.my', 'Community Sdn Bhd');
        $this->dalamTenant($this->t);
        $this->s = app(CommunityService::class);
    }

    // ===============================================================
    // RUANG
    // ===============================================================

    public function test_ruang_tenant_lain_tidak_dijumpai(): void
    {
        $ruang = $this->s->ciptaRuang([
            'name' => 'Ruang Rahsia Tenant Satu',
            'slug' => 'ruang-rahsia',
        ]);

        [, $t2] = $this->daftar('t2@community.my', 'Tenant Dua Sdn Bhd');
        $this->dalamTenant($t2);

        // findOrFail tanpa skop → akan jumpa ruang tenant lain (BOCOR)
        $this->assertNull(
            CommunitySpace::find($ruang->id),
            'CommunitySpace boleh dibaca merentas tenant (IDOR).'
        );
    }

    // ===============================================================
    // SIARAN — inilah laluan URL yang terdedah
    // ===============================================================

    public function test_siaran_tenant_lain_tidak_dijumpai(): void
    {
        $ruang = $this->s->ciptaRuang([
            'name' => 'Ruang Satu',
            'slug' => 'ruang-satu',
        ]);

        $pos = $this->s->ciptaPos($ruang->id, [
            'title' => 'Siaran Rahsia Tenant Satu',
            'body' => 'Kandungan sulit.',
        ]);

        [, $t2] = $this->daftar('t3@community.my', 'Tenant Tiga Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(
            CommunityPost::find($pos->id),
            'CommunityPost boleh dibaca merentas tenant (IDOR /posts/{id}).'
        );
    }

    public function test_api_siaran_tenant_lain_pulang_404(): void
    {
        $ruang = $this->s->ciptaRuang([
            'name' => 'Ruang Dua',
            'slug' => 'ruang-dua',
        ]);

        $pos = $this->s->ciptaPos($ruang->id, [
            'title' => 'Siaran Sulit Satu',
            'body' => 'Jangan bocor.',
        ]);

        $u2 = $this->daftar('t4@community.my', 'Tenant Empat Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant Empat Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        // Mesti 404 — bukan 200 dengan kandungan tenant lain
        $this->getJson('/api/v1/community/posts/' . $pos->id)->assertNotFound();
    }

    // ===============================================================
    // BALASAN
    // ===============================================================

    public function test_balasan_tenant_lain_tidak_dijumpai(): void
    {
        $ruang = $this->s->ciptaRuang(['name' => 'R1', 'slug' => 'r1']);
        $pos = $this->s->ciptaPos($ruang->id, [
            'title' => 'P1',
            'body' => 'B1',
        ]);

        $balasan = $this->s->balas($pos->id, 'Balasan sulit.');

        [, $t2] = $this->daftar('t5@community.my', 'Tenant Lima Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(
            \App\Modules\Community\Models\CommunityReply::find($balasan->id),
            'CommunityReply boleh dibaca merentas tenant (IDOR).'
        );
    }

    // ===============================================================
    // TULIS — tenant_id tidak boleh dipalsukan
    // ===============================================================

    public function test_tidak_boleh_cipta_siaran_untuk_tenant_lain(): void
    {
        [, $t2] = $this->daftar('t6@community.my', 'Tenant Enam Sdn Bhd');
        $this->dalamTenant($this->t);   // konteks = t1

        $this->expectException(\RuntimeException::class);
        CommunityPost::create([
            'tenant_id' => $t2->id,     // ≠ konteks
            'title' => 'Palsu',
            'body' => 'Palsu',
        ]);
    }

    public function test_tenant_id_tidak_boleh_diubah(): void
    {
        $ruang = $this->s->ciptaRuang(['name' => 'R2', 'slug' => 'r2']);

        [, $t2] = $this->daftar('t7@community.my', 'Tenant Tujuh Sdn Bhd');
        $this->dalamTenant($this->t);

        $this->expectException(\RuntimeException::class);
        $ruang->tenant_id = $t2->id;
        $ruang->save();
    }
}
