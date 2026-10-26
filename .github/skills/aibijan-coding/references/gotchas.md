# Pepijat Sebenar & Pembaikan

Semua ini telah berlaku dalam projek ini. Semak senarai ini sebelum menyahpepijat dari kosong.

## Route & Auth

### Route wildcard `api/v1/{id}` menangkap semua endpoint

**Gejala:** setiap endpoint pulangkan 404 atau 500; `AssetController::show()` dipanggil untuk `/api/v1/tenant`.

**Punca:** provider modul daftar `Route::middleware('api')->prefix('api/v1')` tanpa segmen modul. Route `/{id}` menjadi wildcard.

**Pembaikan:**

```php
Route::middleware(['api', 'auth:sanctum', 'tenant'])
    ->prefix('api/v1/assets')
    ->group(__DIR__.'/routes/api.php');
```

**Semakan:**

```powershell
php artisan route:list --path=api/v1 | Select-String "v1/\{"
```

Harus kosong.

### Dua controller untuk jadual sama

**Gejala:** `POST projects/1/tasks` → 404 walaupun projek wujud.

**Punca:** `Api\V1\ProjectController` dan `Modules\Projects\ProjectController` kedua-duanya mendaftar URI. Blok dalam `routes/api.php` mendaftar selepas, jadi ia menang.

**Pembaikan:** pilih satu. Yang digunakan: buang blok modul dari `routes/api.php`, biar modul di prefix `api/v1/pm` melalui provider.

### Prefix berganda `projects/projects`

**Gejala:** route ada dua kali.

**Punca:** provider prefix `projects` + fail route dalaman guna `'/projects'`.

**Pembaikan:** tukar dalaman kepada `'/'`.

### `POST inventory/warehouses` → 422 tiada `type`

**Punca:** Api\V1 menang, dan ia perlukan `type` (`gudang|outlet|dropship|transit`).

**Pembaikan:** guna prefix baharu (`inventory/depots`) untuk controller modul, atau hantar `type`.

## Model & Tenant

### `SQLSTATE: no column named tenant_id` dalam `statutory_rates`

**Punca:** model guna `BelongsToTenant` tetapi migration tiada kolum.

**Pembaikan:** tambah kolum pada migration, **atau** jangan guna trait jika kadar global.

### `MassAssignmentException: Add fillable property [members]`

**Punca:** `members` ialah relasi, bukan kolum.

**Pembaikan:**

```php
$ahli = $validated['members'] ?? [];
unset($validated['members']);
$project = Project::create($validated);
```

### `Rekod tenant tidak boleh dicipta tanpa tenant aktif`

**Punca:** model dicipta tanpa `TenantContext` (biasanya dalam ujian atau job).

**Pembaikan:** `$this->dalamTenant($tenant)` dalam ujian; `TenantContext::set()` dalam job.

### `Class "App\Models\CrmClient" not found`

**Punca:** relasi merujuk model tidak wujud.

**Pembaikan:** guna model sebenar — `customers` wujud, `crm_clients` tidak.

### `table has no column named role`

**Punca:** controller hantar medan tiada dalam skema (`project_members` tiada `role`).

**Pembaikan:** buang medan dari validasi dan penciptaan.

### `table has no column named updated_at`

**Punca:** jadual tanpa timestamps tetapi model tidak set `$timestamps = false`.

**Pembaikan:** `public $timestamps = false;`

## Nombor & Pembundaran

### Ujian jangka `'100.00'` tetapi dapat `100`

**Punca:** model tiada cast `decimal:2`. SQLite pulangkan float.

**Pembaikan:**

```php
protected $casts = ['amount' => 'decimal:2', 'book_value' => 'decimal:2'];
```

### EIS pulangkan `8.333333` bukan `8.33`

**Punca:** `$maxAnnual / 12` tidak dibundarkan.

**Pembaikan:**

```php
$maxMonthly = round($maxAnnual / 12, 2);
return min(round($monthlyContribution, 2), $maxMonthly);
```

### OT tersasar RM0.02

**Punca:** kadar sejam dibundarkan ke 2 tempat sebelum didarab.

**Pembaikan:** simpan 4 tempat untuk pengiraan, bundar hanya untuk paparan.

```php
return round($basicSalary / self::HOURS_PER_MONTH, 4);
```

### Kredit AI sama untuk semua tier

**Punca:** `max($credits, 100)` menyebabkan nilai kecil semua jadi 100.

**Pembaikan:** minimum mesti berkadar dengan tier.

```php
return max($credits, (int) round($minimum * $multiplier));
```

## Ujian

### `UniqueConstraintViolationException` pada `ai_model_catalog`

**Punca:** seeder sudah cipta `provider_model_id` yang ujian cuba cipta.

**Pembaikan:** `AiModel::firstOrCreate([...], [...])`

### `Attempt to read property "id" on null`

**Punca:** `Model::latest()->first()` dalam ujian tanpa `TenantContext`.

**Pembaikan:** guna `->json('id')` dari respons HTTP, bukan query semula.

### `404` pada `GET /api/v1/projects/1`

**Punca:** route `/projects/{id}` dipegang controller yang perlukan `owner_id` atau keahlian, tetapi projek dicipta tanpa `owner_id`.

**Pembaikan:** set `owner_id` semasa cipta:

```php
$validated['owner_id'] ??= auth()->id();
```

### `array_column` + `->map()` pada array

**Punca:** `array_column` pulangkan array, bukan Collection.

**Pembaikan:**

```php
$ids = collect($data)->map(fn ($x) => $x->id)->unique()->values()->all();
```

## API

### `assertJsonCount(0)` dapat 13

**Punca:** respons ialah paginator (13 kunci), bukan array.

**Pembaikan:** guna `->json('data')`:

```php
$this->assertCount(0, $this->getJson('/api/v1/x')->json('data'));
```

## AI Gateway

### `ArgumentCountError` selepas tambah dependency

**Punca:** binding singleton masih beri 1 argumen.

**Pembaikan:**

```php
$this->app->singleton(GatewayService::class, function ($app) {
    return new GatewayService(
        $app->make(PiiRedactor::class),
        $app->make(PromptGuard::class)
    );
});
```

### Prompt injection tidak disekat dalam mod simulasi

**Punca:** imbasan diletak selepas feature flag `live_calls_enabled`.

**Pembaikan:** imbasan mesti langkah **0**, sebelum apa-apa.

### `logUsage(): Argument #5 must be AiModel, null given`

**Punca:** cuba log kegagalan sebelum model dipilih.

**Pembaikan:** guna `Log::warning()` untuk peringkat awal, bukan `logUsage()`.

### `Class "App\Modules\AIGateway\PromptGuard" does not exist`

**Punca:** import hilang dalam service provider.

**Pembaikan:** tambah `use App\Modules\AIGateway\Services\PromptGuard;`.

## PowerShell

### `routes/api.php` jadi satu baris, 765 Route tercantum

**Punca:** `Set-Content -NoNewline` pada array baris.

**Pembaikan:** tiada rollback tanpa git. Guna skrip PHP yang pulihkan pemisah baris dan indentasi, atau pulihkan dari sandaran.

**Pencegahan:** `Copy-Item routes\api.php routes\api.php.bak` sebelum suntingan pukal; guna `replace_string_in_file`.

### `-First` atau `-Skip` tergantung pada baris PowerShell baharu

**Punca:** `Select-Object -First 25` di hujung baris tanpa backtick dianggap parameter `ForEach-Object`.

**Pembaikan:** asingkan dengan `;` atau letak dalam kurungan.

---

## Laravel / PHPUnit (dari BAB 82)

### Alias middleware ialah `perm:`, bukan `permission:`

**Punca:** `bootstrap/app.php` mendaftarkan alias `perm` sahaja.

**Ralat:** `Target class [permission] does not exist.` — semua ujian HTTP gagal.

**Pembaikan:** guna `->middleware('perm:blast.view')`.

### Enum `NOT NULL` dalam SQLite perlukan `$attributes` dalam model

**Punca:** SQLite tidak menghantar default jadual bila Eloquent `create()` dipanggil
tanpa medan tersebut.

**Ralat:** `NOT NULL constraint failed: prospects.status`

**Pembaikan:** tambah dalam model:

```php
protected $attributes = [
    'status' => self::STATUS_NEW,
    'consent_status' => self::CONSENT_UNKNOWN,
];
```

### `DB::raw()` dengan datetime MESTIL ada tanda petik

**Ralat:** `SQLSTATE[HY000]: General error: 1 near "23": syntax error`

**Pembaikan:**

```php
// SALAH
'first_touched_at' => DB::raw('COALESCE(first_touched_at, '.now()->toDateTimeString().')'),

// BETUL
'first_touched_at' => DB::raw("COALESCE(first_touched_at, '".now()->toDateTimeString()."')"),
```

### Method signature `array` menolak Eloquent Collection

**Ralat:** `Argument #2 ($prospects) must be of type array, Collection given`

**Pembaikan:** guna `iterable` dalam type hint:

```php
public function binaPesanan(BlastCampaign $campaign, iterable $prospects, ?string $template = null): array
```

### Tipe hint `array` pada `Illuminate\Support\Collection` — gunakan `iterable`

**Punca:** controller hantar `$query->get()` (Collection) kepada service yang
signature-nya `array`.

### Namakan kaedah ujian tanpa ruang

**Ralat:** `syntax error, unexpected identifier "pasti", expecting "("`

**Punca:** nama kaedah seperti `test_x coisa()` inadvertently ada space.

**Pencegahan:** jalankan `php -l tests/Feature/XxxTest.php` sebelum jalankan suite.

### `replace_string_in_file` gagal pada teks dengan aksara CJK

**Punca:** aksara CJK/Arabic tersusukan menyebabkan padding byte tidak sepadan.

**Pembaikan:** guna skrip PHP dengan `preg_replace('/[\x{4e00}-\x{9fff}]+/u', '', $c)`.

### Akhbar CJK/Arabic dalam fail PHP — semak selepas tulis

**Punca:** IME mungkin menyisip aksara CJK semasa menaip komen/label BM.

**Pencegahan:** selepas creating fail PHP/JS yang panjang, jalankan:

```powershell
php -l file.php
Select-String -Path file.php -Pattern '[\u4e00-\u9fff\u0600-\u06ff]' # mesti kosong
```

### Pagar consent wajib di BLAST — jangan bypass

**Peraturan:** `ConsentService::bolehDihantar()` mesti dipanggil SEBELUM
membuat `blast_messages`. Prospect tanpa `consent_status='granted'` atau tanpa
`consent_evidence` → `skipped`, bukan `queued`.

### `kiraReputasi()` mesti `save()` sendiri

**Punca:** jika service hanya mengubah attribute tanpa `save()`, status `is_paused`
tidak kekal dan ujian gagal.

**Pembaikan:** `save()` di dalam method, bukan hanya di caller.

### Route modul — prefix Wajib ada

**Peraturan:** setiap modul yang daftar route sendiri MESTIL ada prefix sendiri,
cth `api/v1/blast`. Tanpa prefix → wildcard `api/v1/{id}` dan route `routes/api.php`
kalah.

**Semak:** `php artisan route:list --path=api/v1 | Select-String "v1/\{"` → mesti kosong.

---

## Filament — resource tidak muncul / halaman 500 sunyi

### 🔴 `IconColumn`/`TextColumn` closure mesti `fn ($record)`, bukan `fn ($s)`

Filament menyuntik parameter closure **mengikut NAMA**. `fn ($s) => ...` menghasilkan:

```
Illuminate\Contracts\Container\BindingResolutionException
An attempt was made to evaluate a closure for [Filament\Tables\Columns\IconColumn],
but [$s] was unresolvable.
```

Halaman pulang 500 tetapi **ujian unit LULUS** — hanya kelihatan dalam pelayar sebenar.

**Nama parameter sah:** `$record`, `$state`, `$column` (dan `$row`, `$livewire`).

```php
// ❌ 500 sunyi
Tables\Columns\IconColumn::make('x')->boolean(fn ($s) => $s->isOk()),

// ✅
Tables\Columns\IconColumn::make('x')->boolean(fn ($record) => $record->isOk()),
```

**Sahkan dalam pelayar:** buka halaman, semak `page.textContent('body')` untuk
`BindingResolutionException`. Ujian HTTP pelayar tidak cukup — Livewire render selepas mount.

### 🔴 `discoverResources` perlukan `getPages()` pada setiap resource

Folder `Pages/` + resource class **TIDAK cukup**. Tanpa `getPages()`:

```
Route [filament.admin.resources.X.index] not defined
```

Setiap resource perlukan **kelima-lima**:
1. `Pages/ListX.php` (`extends ListRecords`)
2. `Pages/CreateX.php` (`extends CreateRecord`)
3. `Pages/EditX.php` (`extends EditRecord`)
4. `Pages/ViewX.php` (`extends ViewRecord`)
5. `public static function getPages(): array` dalam resource

```php
public static function getPages(): array
{
    return [
        'index'  => Pages\ListX::route('/'),
        'create' => Pages\CreateX::route('/create'),
        'view'   => Pages\ViewX::route('/{record}'),
        'edit'   => Pages\EditX::route('/{record}/edit'),
    ];
}
```

⚠️ **JANGAN** `use Filament\Resources\Pages;`. Resource dalam
`App\Filament\Resources\X\` mesti resolve `Pages\ListX` **secara relatif** ke
`App\Filament\Resources\X\Pages\ListX`. Import eksplisit memecahkan rujukan.

---

## Migrasi & Data

### `UNIQUE(tenant_id, key)` tidak menghalang kunci global bertindih

SQLite/MySQL menganggap `NULL` sebagai **berbeza** dalam indeks unik komposit. Jadi
`UNIQUE(tenant_id, key)` membenarkan DUA baris `key='x'` apabila satu `tenant_id` NULL
(skill/templat global) dan satu tenant sebenar — kemudian `insert` ketiga pecah dengan
`UNIQUE constraint failed: table.key` jika `key` masih unique global.

**Peraturan:** jika jadual menyokong baris global (`tenant_id` NULL) + salinan per-tenant,
**jangan** letak `->unique()` pada kolum `key`. Buat semakan unik dalam servis:

```php
private function kunciWujud(int $tenantId, string $key): bool
{
    return Model::withoutGlobalScopes()
        ->where('key', $key)
        ->where(fn ($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
        ->exists();
}
```

### `TenantUser` tiada `$fillable` untuk `role` / `tenant_id`

`tenant_users` **tiada kolum `role`** — peranan melalui pivot `roles()`.
`TenantUser::create([...'role'=>...])` → `MassAssignmentException` atau
`table tenant_users has no column named role`.

**Guna `TenantProvisioner::create($nama, $owner, $planCode)`** — ia mengendalikan
peranan, langganan dan pemilik. Jangan bina keahlian secara manual.

### Selepas `migrate:fresh`, data dev hilang

```powershell
php artisan db:seed --force          # plans + katalog model AI
php skrip/cipta-tenant-ujian.php     # pengguna + tenant + plan enterprise
```

### Ruang tersembunyi merosakkan nama fungsi

Menulis fail panjang boleh menyelitkan ruang/CJK dalam nama pengenal:
`$this->kemas kiniStatistik($id)` → `syntax error, unexpected identifier "kiniStatistik"`.

**Semak selepas tulis:**
```powershell
php -r '$s=file_get_contents("path.php");
if (preg_match("/[\x{4e00}-\x{9fff}\x{0600}-\x{06ff}]/u",$s,$m)) echo "CJK: ".$m[0]."\n";'
```
Guna `php -l` pada setiap fail baharu sebelum jalankan ujian.
