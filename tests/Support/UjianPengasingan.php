<?php

namespace Tests\Support;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * Ujian pengasingan tenant GENERIK untuk satu modul.
 *
 * Ditambah 27 Okt 2026 (P1-1d) selepas `docs/AUDIT-TENANT-ISOLATION.md`
 * mendapati hanya 4 daripada 58 modul mempunyai ujian pengasingan sendiri.
 * "Tidak bocor" tanpa ujian ialah **andaian**, bukan bukti.
 *
 * Setiap subclass menetapkan `$modul` dan `$model`, kemudian mewarisi
 * 5 ujian teras. Jika model memerlukan kolum wajib, timpa `isi()`.
 *
 * Yang diuji — sama seperti `FinanceTenantIsolationTest`:
 *   1. BACA     — `find()` tidak menembusi skop tenant lain
 *   2. BACA     — `count()` konteks t2 tidak nampak rekod t1
 *   3. TULIS    — `create()` tidak boleh memalsukan `tenant_id`
 *   4. TULIS    — `save()` tidak boleh menukar `tenant_id`
 *   5. API      — laluan modul tidak memulangkan rekod tenant lain
 *
 * ⚠️ Rekod MESTI dicipta melalui konteks tenant (`dalamTenant`) — trait
 * `BelongsToTenant` membuang `tenant_id` eksplisit yang berbeza.
 */
abstract class UjianPengasingan extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    /** Nama folder dalam `app/Modules/` — WAJIB ditetapkan oleh subclass. */
    protected string $modul = '';

    /** Model yang diuji. */
    protected array $model = [];

    /** Laluan API modul — pilihan (null = ujian API dilangkau). */
    protected ?string $laluanApi = null;

    /**
     * Sebab ujian API tiada — didokumenkan supaya skip mudah difahami
     * (contoh: modul awam, kawalan di peringkat laluan lain).
     */
    protected string $sebabTiadaApi = '';

    /** Teks rahsia yang TIDAK boleh muncul dalam response tenant lain. */
    protected ?string $namaRahsia = null;

    protected ?Tenant $t = null;

    protected ?Tenant $t2 = null;

    /** Pengguna pemilik tenant satu — untuk kolum `created_by`/`user_id`. */
    protected $u = null;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->modul === '') {
            throw new RuntimeException('Ujian pengasingan mesti menetapkan $modul.');
        }

        $this->setUpAkses();

        [$this->u, $this->t] = $this->daftar(
            'pemilik@' . strtolower($this->modul) . '.my',
            $this->modul . ' Sdn Bhd'
        );
        $this->dalamTenant($this->t);
    }

    // ===============================================================
    // Cipta rekod — TIMPA `isi()` jika model perlu kolum wajib
    // ===============================================================

    /**
     * Medan untuk satu rekod ujian.
     */
    protected function isi(): array
    {
        return [];
    }

    /** Model yang sedang dicipta — untuk `isi()` yang berbeza setiap model. */
    protected string $rekodSemasa = '';

    /**
     * Guna `forceCreate` (lalu `$fillable`). Perlu apabila jadual diwarisi
     * mempunyai kolum WAJIB yang tidak disenaraikan dalam `$fillable`
     * (contoh `assets.tag`, `assets.cost`).
     */
    protected bool $gunaForce = false;

    protected function rekod(string $model): Model
    {
        $this->rekodSemasa = $model;
        $data = $this->isi();

        return $this->gunaForce ? $model::forceCreate($data) : $model::create($data);
    }

    protected function konteksDua(): Tenant
    {
        if ($this->t2 === null) {
            [, $this->t2] = $this->daftar(
                't2@' . strtolower($this->modul) . '.my',
                $this->modul . ' Tenant Dua Sdn Bhd'
            );
        }

        $this->dalamTenant($this->t2);

        return $this->t2;
    }

    // ===============================================================
    // 1-2. BACA
    // ===============================================================

    public function test_tenant_lain_tidak_boleh_baca_rekod(): void
    {
        if ($this->model === []) {
            $this->markTestSkipped('Modul ' . $this->modul . ': tiada model — ujian kontrak sahaja.');
        }

        foreach ($this->model as $kelas) {
            $this->dalamTenant($this->t);
            $rekod = $this->rekod($kelas);

            $this->konteksDua();

            $this->assertNull(
                $kelas::find($rekod->getKey()),
                $kelas . ': find() menembusi skop tenant lain (IDOR) — modul ' . $this->modul . '.'
            );

            // Rekod memang wujud untuk tenant asal — bukan gagal cipta sahaja
            $this->assertSame(
                1,
                $kelas::withoutGlobalScope('tenant')->whereKey($rekod->getKey())->count(),
                $kelas . ': rekod tenant satu hilang — cipta() gagal, bukan terasing.'
            );
        }
    }

    public function test_tenant_lain_tidak_nampak_senarai(): void
    {
        if ($this->model === []) {
            $this->markTestSkipped('Modul ' . $this->modul . ': tiada model — ujian kontrak sahaja.');
        }

        foreach ($this->model as $kelas) {
            $this->dalamTenant($this->t);
            $this->rekod($kelas);

            $this->konteksDua();

            $this->assertSame(
                0,
                $kelas::count(),
                $kelas . ': count() konteks tenant dua nampak rekod tenant satu — modul ' . $this->modul . '.'
            );
        }
    }

    // ===============================================================
    // 3-4. TULIS
    // ===============================================================

    public function test_tidak_boleh_cipta_untuk_tenant_lain(): void
    {
        if ($this->model === []) {
            $this->markTestSkipped('Modul ' . $this->modul . ': tiada model — ujian kontrak sahaja.');
        }

        $t2 = $this->konteksDua();
        $this->dalamTenant($this->t);   // konteks t1, cuba tulis t2

        $kelas = $this->model[0];

        $this->expectException(RuntimeException::class);
        $kelas::create(array_merge($this->isi(), ['tenant_id' => $t2->id]));
    }

    public function test_tenant_id_tidak_boleh_diubah(): void
    {
        if ($this->model === []) {
            $this->markTestSkipped('Modul ' . $this->modul . ': tiada model — ujian kontrak sahaja.');
        }

        $this->dalamTenant($this->t);
        $rekod = $this->rekod($this->model[0]);

        $t2 = $this->konteksDua();
        $this->dalamTenant($this->t);

        $this->expectException(RuntimeException::class);
        $rekod->tenant_id = $t2->id;
        $rekod->save();
    }

    // ===============================================================
    // 5. Laluan API — pilihan
    // ===============================================================

    public function test_api_tenant_lain_tidak_nampak_rekod(): void
    {
        if ($this->model === []) {
            $this->markTestSkipped('Modul ' . $this->modul . ': tiada model — ujian kontrak sahaja.');
        }

        if ($this->laluanApi === null) {
            $this->markTestSkipped(
                'Modul ' . $this->modul . ': tiada laluan API ditetapkan'
                . ($this->sebabTiadaApi !== '' ? ' — ' . $this->sebabTiadaApi : ' (ujian model sahaja).')
            );
        }

        $this->dalamTenant($this->t);
        $this->rekod($this->model[0]);

        // daftar() membuat permintaan HTTP → konteks kosong (nota projek)
        [$u2, $t2] = $this->daftar(
            'api2@' . strtolower($this->modul) . '.my',
            $this->modul . ' API Dua Sdn Bhd'
        );
        $this->sebagai($u2, $t2);

        $respons = $this->getJson($this->laluanApi);

        // 403 = kebenaran/entitlemen tiada — tidak mendedahkan data, lulus
        if ($respons->status() === 403) {
            $this->assertStringNotContainsString(
                (string) $this->namaRahsia,
                $respons->getContent(),
                'Response 403 masih mengandungi data tenant lain — modul ' . $this->modul . '.'
            );

            return;
        }

        $respons->assertOk();

        if ($this->namaRahsia !== null) {
            $this->assertStringNotContainsString(
                $this->namaRahsia,
                $respons->getContent(),
                'API modul ' . $this->modul . ' memulangkan rekod tenant lain (IDOR).'
            );
        }
    }

    /**
     * Elak pencemaran konteks antara ujian dalam proses yang sama.
     */
    protected function tearDown(): void
    {
        app(TenantContext::class)->clear();

        parent::tearDown();
    }
}
