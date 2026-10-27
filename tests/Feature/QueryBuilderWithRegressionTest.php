<?php

namespace Tests\Feature;

use App\Modules\SCM\Models\PurchaseOrder;
use App\Modules\SCM\Models\Supplier;
use App\Modules\Finance\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Regresi: `DB::table()->with()` — PEPIJAT PENGELUARAN.
 *
 * ⚠️ `with()` TIDAK wujud pada query builder Laravel. Panggilan seperti
 *
 *     DB::table('journal_entries')->with(['lines.account'])->paginate(20);
 *
 * menghasilkan fatal error:
 *     Call to undefined method Illuminate\Database\Query\Builder::with()
 *
 * Lint PHP (`php -l`) TIDAK mengesan ini — ia ralat runtime, bukan sintaks.
 * Ujian ini memastikan setiap endpoint senarai & butiran benar-benar dipanggil,
 * supaya pepijat jenis ini tidak kembali.
 */
class QueryBuilderWithRegressionTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('qa@regresi.my', 'Syarikat Regresi');
        $this->dalamTenant($this->t);
    }

    // ===================================================================
    // PENGESAN STATIK — halang corak berbahaya kembali
    // ===================================================================

    #[Test]
    public function tiada_db_table_dengan_with_dalam_kod(): void
    {
        $bermasalah = [];
        $akar = base_path('app');

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($akar, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $f) {
            if (! $f->isFile() || $f->getExtension() !== 'php') {
                continue;
            }

            $kod = file_get_contents($f->getPathname());

            // ⚠️ Buang komen DAHULU — komen menerangkan pepijat dan akan
            // menghasilkan positif palsu jika tidak dibuang.
            $tanpaKomen = preg_replace(
                ['~/\*.*?\*/~s', '~//[^\n]*~', '~#[^\n]*~'],
                '',
                $kod
            );

            // Cari `DB::table(...)` diikuti `->with(` (abaikan komen)
            if (preg_match('/DB::table\([^;]{0,300}?->\s*with\s*\(/s', $tanpaKomen)) {
                $bermasalah[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $f->getPathname());
            }
        }

        $this->assertSame(
            [],
            $bermasalah,
            "DB::table()->with() TIDAK SAH (fatal runtime). Guna Eloquent Model::query()->with().\n"
            . 'Fail: ' . implode(', ', $bermasalah)
        );
    }

    /**
     * `Model::find()` hanya selamat JIKA model guna trait `BelongsToTenant` —
     * global scope menapis ikut tenant semasa. Jika TIADA trait, `find()`
     * memintas pengasingan → risiko IDOR merentas tenant.
     *
     * Ujian ini menyenaraikan `find()` dan menyemak trait pada model terbabit.
     */
    #[Test]
    public function find_hanya_digunakan_pada_model_bertrait_tenant(): void
    {
        $bermasalah = [];

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                base_path('app/Modules'),
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($it as $f) {
            if (! $f->isFile() || ! str_contains(str_replace('\\', '/', $f->getPathname()), 'Controllers')) {
                continue;
            }

            $kod = preg_replace(
                ['~/\*.*?\*/~s', '~//[^\n]*~', '~#[^\n]*~'],
                '',
                file_get_contents($f->getPathname())
            );

            if (! preg_match_all('/(\\\\?[\w\\\\]+)::find\s*\(/', $kod, $m)) {
                continue;
            }

            foreach (array_unique($m[1]) as $pendek) {
                $kelas = $this->selesaikanKelas($pendek, $f->getPathname());

                if ($kelas === null) {
                    continue;   // tidak dapat selesaikan — abai
                }

                // ⚠️ `Tenant` sendiri TIDAK di-skop tenant (ia ADALAH tenant).
                // Model platform lain juga sah tanpa trait.
                $sahTanpaTrait = [
                    \App\Models\Tenant::class,
                    \App\Models\User::class,
                    \App\Models\Plan::class,
                ];

                if (in_array($kelas, $sahTanpaTrait, true)) {
                    continue;
                }

                $gunaTrait = in_array(
                    \App\Models\Concerns\BelongsToTenant::class,
                    class_uses_recursive($kelas),
                    true
                );

                if (! $gunaTrait) {
                    $bermasalah[] = str_replace(
                        base_path().DIRECTORY_SEPARATOR, '', $f->getPathname()
                    ).' → '.$pendek.'::find() TANPA trait';
                }
            }
        }

        $this->assertSame(
            [],
            $bermasalah,
            "::find() pada model TANPA BelongsToTenant = risiko IDOR merentas tenant.\n"
            . "Guna: Model::query()->where('id',\$id)->where('tenant_id',\$tid)->first()\n"
            . implode("\n", $bermasalah)
        );
    }

    /**
     * Cuba selesaikan nama kelas pendek kepada FQCN berdasarkan import dalam fail.
     */
    private function selesaikanKelas(string $pendek, string $fail): ?string
    {
        // Sudah FQCN
        if (str_starts_with($pendek, '\\') || str_contains($pendek, '\\')) {
            $fq = ltrim($pendek, '\\');

            return class_exists($fq) ? $fq : null;
        }

        // Cari `use ...\Nama;` dalam fail
        $kod = file_get_contents($fail);

        if (preg_match('/^use\s+([\w\\\\]*\\\\'.preg_quote($pendek, '/').');/m', $kod, $m)) {
            return class_exists($m[1]) ? $m[1] : null;
        }

        // Cuba namespace modul semasa
        if (preg_match('/^namespace\s+([\w\\\\]+);/m', $kod, $m)) {
            $modul = explode('\\', $m[1]);
            $calon = $modul[0].'\\'.$modul[1].'\\'.$modul[2].'\\Models\\'.$pendek;

            if (class_exists($calon)) {
                return $calon;
            }
        }

        return null;
    }

    // ===================================================================
    // KEWANGAN — laluan yang sebelum ini 500
    // ===================================================================

    #[Test]
    public function chart_of_accounts_senarai_tidak_crash(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/finance/chart-of-accounts');

        $res->assertOk();
    }

    #[Test]
    public function journal_entries_senarai_tidak_crash(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/finance/journal-entries');

        $res->assertOk();
    }

    #[Test]
    public function journal_entry_show_pulang_404_bukan_500(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/finance/journal-entries/999999');

        $res->assertNotFound();
    }

    #[Test]
    public function journal_entry_dengan_relasi_dimuat(): void
    {
        $entry = JournalEntry::create([
            'tenant_id'      => $this->t->id,
            'entry_number'   => 'JE-REGRESI-001',
            'entry_date'     => now()->toDateString(),
            'description'    => 'Ujian regresi',
            'status'         => 'draft',
        ]);

        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/finance/journal-entries/'.$entry->id);

        $res->assertOk();
        $this->assertSame('JE-REGRESI-001', $res->json('entry_number'));
    }

    // ===================================================================
    // SCM — laluan yang sebelum ini 500
    // ===================================================================

    #[Test]
    public function suppliers_senarai_tidak_crash(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/scm/suppliers');

        $res->assertOk();
    }

    #[Test]
    public function supplier_show_pulang_404_bukan_500(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/scm/suppliers/999999');

        $res->assertNotFound();
    }

    #[Test]
    public function purchase_orders_senarai_tidak_crash(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/scm/purchase-orders');

        $res->assertOk();
    }

    // ===================================================================
    // PENGASINGAN TENANT — model yang baru ditukar mesti masih terasing
    // ===================================================================

    #[Test]
    public function journal_entry_tenant_lain_tidak_nampak(): void
    {
        [$u2, $t2] = $this->daftar('lain@regresi.my', 'Syarikat Lain');

        $this->dalamTenant($t2);
        $entryLain = JournalEntry::create([
            'tenant_id'    => $t2->id,
            'entry_number' => 'JE-LAIN-001',
            'entry_date'   => now()->toDateString(),
            'description'  => 'Punya tenant lain',
            'status'       => 'draft',
        ]);

        // ⚠️ daftar() melakukan log masuk HTTP sebenar — sesi kini milik u2.
        // Mesti log masuk semula sebagai u1 untuk ujian bermakna.
        $this->dalamTenant($this->t);

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/finance/journal-entries/'.$entryLain->id);

        // ⚠️ Mesti 404 — bukan 200
        $res->assertNotFound();
    }

    #[Test]
    public function supplier_tenant_lain_tidak_nampak(): void
    {
        [$u2, $t2] = $this->daftar('lain2@regresi.my', 'Syarikat Lain 2');

        $this->dalamTenant($t2);
        $supLain = Supplier::create([
            'tenant_id'    => $t2->id,
            'supplier_code'=> 'SUP-LAIN-001',
            'company_name' => 'Pembekal Tenant Lain',
        ]);

        $this->dalamTenant($this->t);

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/scm/suppliers/'.$supLain->id);

        $res->assertNotFound();
    }

    #[Test]
    public function senarai_tidak_terdedah_merentas_tenant(): void
    {
        [$u2, $t2] = $this->daftar('lain3@regresi.my', 'Syarikat Lain 3');

        $this->dalamTenant($t2);
        JournalEntry::create([
            'tenant_id'    => $t2->id,
            'entry_number' => 'JE-ASING-001',
            'entry_date'   => now()->toDateString(),
            'description'  => 'Hanya untuk tenant 2',
            'status'       => 'draft',
        ]);

        $this->dalamTenant($this->t);

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/finance/journal-entries');

        $res->assertOk();

        // Pastikan nombor entri tenant lain TIDAK muncul
        $this->assertStringNotContainsString('JE-ASING-001', $res->getContent());
    }

    // ===================================================================
    // PENGESAN: tenant_id MESTI dari konteks, bukan header
    // ===================================================================

    #[Test]
    public function header_x_tenant_id_tidak_dibenarkan_menukar_skop(): void
    {
        [$u2, $t2] = $this->daftar('lain4@regresi.my', 'Syarikat Lain 4');

        $this->dalamTenant($t2);
        JournalEntry::create([
            'tenant_id'    => $t2->id,
            'entry_number' => 'JE-IDOR-001',
            'entry_date'   => now()->toDateString(),
            'description'  => 'Ujian IDOR',
            'status'       => 'draft',
        ]);

        $this->dalamTenant($this->t);

        // Cuba menipu dengan header lama yang pernah digunakan
        $res = $this->sebagai($this->u, $this->t)
            ->withHeader('X-Tenant-ID', (string) $t2->id)
            ->getJson('/api/v1/finance/journal-entries');

        $res->assertOk();

        // ⚠️ Header TIDAK boleh membuka data tenant lain
        $this->assertStringNotContainsString(
            'JE-IDOR-001',
            $res->getContent(),
            'IDOR: header X-Tenant-ID membocorkan data tenant lain!'
        );
    }
}
