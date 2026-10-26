# Prosedur Menambah Modul

## 1. Migration (global)

`database/migrations/2026_MM_DD_NNNNNN_create_<jadual>.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contoh_borang', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->string('name', 150);
            $t->enum('status', ['draf', 'aktif', 'arkib'])->default('draf');
            $t->decimal('amount', 18, 2)->default(0);
            $t->date('retention_until')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['tenant_id', 'status']);
            $t->unique(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contoh_borang');
    }
};
```

**Peraturan skema:**

- Setiap jadual perniagaan: `tenant_id` + FK `cascadeOnDelete`
- Indeks bermula dengan `tenant_id`
- Wang: `decimal(18,2)`, bukan float
- Jadual anak: tiada `tenant_id` jika diwarisi induk
- Jangan rujuk jadual yang belum wujud dalam FK (contoh `branches`) — guna `unsignedBigInteger` nullable

## 2. Model

`app/Modules/<Nama>/Models/<Model>.php`

```php
<?php

namespace App\Modules\Contoh\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ContohBorang extends Model
{
    use BelongsToTenant;

    protected $table = 'contoh_borang';

    protected $fillable = ['tenant_id', 'name', 'status', 'amount', 'retention_until'];

    protected $casts = [
        'amount' => 'decimal:2',
        'retention_until' => 'date',
    ];

    public const STATUS = ['draf', 'aktif', 'arkib'];
}
```

**Cast wajib untuk decimal.** Tanpanya SQLite pulangkan float, MySQL pulangkan string — ujian jadi tidak konsisten antara DB.

## 3. Servis

`app/Modules/<Nama>/Services/<Nama>Service.php`

Logik perniagaan di sini, bukan dalam controller. Operasi berbilang jadual guna `DB::transaction()`.

```php
public function cipta(array $d): ContohBorang
{
    return DB::transaction(function () use ($d) {
        $rekod = ContohBorang::create($d);
        app(AuditLogger::class)->log('contoh.created', $rekod, null, ['name' => $rekod->name]);

        return $rekod;
    });
}
```

## 4. Controller

`app/Modules/<Nama>/Http/Controllers/<Nama>Controller.php`

```php
<?php

namespace App\Modules\Contoh\Http\Controllers;

use App\Http\Controllers\Controller;   // WAJIB: tiada base class dalam modul
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class ContohBorangController extends Controller
{
    private function tid(): int
    {
        return app(TenantContext::class)->id();
    }

    public function index()
    {
        return response()->json(ContohBorang::orderByDesc('id')->paginate(50));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:150',
            'status' => 'nullable|in:'.implode(',', ContohBorang::STATUS),
            'amount' => 'nullable|numeric|min:0',
        ]);

        return response()->json(ContohBorang::create($d), 201);
    }

    public function show(int $id)
    {
        return response()->json(ContohBorang::findOrFail($id));
    }
}
```

## 5. Permission

`config/aibijan.php` — tambah pada blok `permissions`:

```php
'contoh.view' => 'Lihat rekod contoh',
'contoh.manage' => 'Urus rekod contoh',
'contoh.approve' => 'Lulus rekod contoh',
```

Kemudian tambah pada peranan berkaitan dalam blok `roles`.

**Peraturan:** permission menulis data AI/knowledge (`personal_ai.configure`) hanya untuk pengurusan — bukan `staff`/`auditor`/`cashier`.

## 6. Route

Dalam `routes/api.php`, di dalam blok `Route::middleware('tenant')->group()`:

```php
Route::middleware('perm:contoh.view')->group(function () {
    Route::get('contoh', [\App\Modules\Contoh\Http\Controllers\ContohBorangController::class, 'index']);
    Route::get('contoh/{id}', [\App\Modules\Contoh\Http\Controllers\ContohBorangController::class, 'show'])->whereNumber('id');
});
Route::middleware('perm:contoh.manage')->group(function () {
    Route::post('contoh', [\App\Modules\Contoh\Http\Controllers\ContohBorangController::class, 'store']);
});
```

**Wajib:** `->whereNumber('id')` pada setiap parameter angka.

## 7. Ujian

`tests/Feature/ContohTest.php`

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

class ContohTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@kedai-a.my', 'Kedai A');
    }

    public function test_cipta_dan_senarai(): void
    {
        $r = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/contoh', ['name' => 'Ujian'])
            ->assertCreated()
            ->json();

        $this->assertSame('Ujian', $r['name']);

        $senarai = $this->getJson('/api/v1/contoh')->assertOk()->json('data');
        $this->assertCount(1, $senarai);
    }

    public function test_tenant_lain_tidak_nampak(): void
    {
        $this->sebagai($this->u, $this->t)->postJson('/api/v1/contoh', ['name' => 'A'])->assertCreated();

        [$ub, $tb] = $this->daftar('pemilik@kedai-b.my', 'Kedai B');
        $r = $this->sebagai($ub, $tb)->getJson('/api/v1/contoh')->assertOk()->json('data');

        $this->assertCount(0, $r);   // WAJIB: ujian isolasi tenant
    }

    public function test_staf_tidak_boleh_cipta(): void
    {
        $staf = $this->jemput($this->u, $this->t, 'staf@kedai-a.my', ['staff']);

        $this->sebagai($staf, $this->t)
            ->postJson('/api/v1/contoh', ['name' => 'X'])
            ->assertForbidden();     // WAJIB: ujian RBAC
    }
}
```

## 8. Jalankan

```powershell
php artisan test --filter=ContohTest
php artisan test
```

## Templat Ujian: Servis Terus

Jika menguji servis tanpa HTTP:

```php
public function test_servis_terus(): void
{
    $this->dalamTenant($this->t);          // WAJIB sebelum cipta model

    $rekod = ContohBorang::create(['name' => 'X']);
    $this->assertNotNull($rekod->id);
}
```
