# Peraturan Keselamatan

## Isolasi Tenant

### Empat lapisan pertahanan

| Lapisan | Mekanisme | Ujian |
|---|---|---|
| 1 | Global scope `BelongsToTenant` menapis semua query | `assertCount(0, ...)` untuk tenant lain |
| 2 | FK `tenant_id` + `cascadeOnDelete` | Migrasi |
| 3 | Middleware `ResolveTenant` sahkan keahlian | `assertForbidden()` |
| 4 | Controller/policy periksa objek | `assertNotFound()` atau `assertForbidden()` |

**Lapisan 1 sahaja tidak cukup.** Ia boleh dipintas melalui `withoutGlobalScope()` atau relasi mentah.

### Ujian isolasi wajib untuk setiap modul

```php
public function test_tenant_lain_tidak_nampak(): void
{
    $this->sebagai($ua, $ta)->postJson('/api/v1/x', [...])->assertCreated();

    [$ub, $tb] = $this->daftar('b@kedai-b.my', 'Kedai B');
    $this->sebagai($ub, $tb)->getJson('/api/v1/x')->assertOk()->assertJsonCount(0, 'data');
}
```

### Jangan percaya `tenant_id` dari klien

```php
// SALAH
$item->tenant_id = $request->input('tenant_id');

// BETUL
$item->tenant_id = app(TenantContext::class)->id();
```

## Permission

### Permission menulis AI = pengurusan sahaja

`personal_ai.configure` membenarkan pengguna menulis ke pangkalan pengetahuan AI — ini **mencemarkan konteks** untuk semua pengguna. Jangan beri kepada `staff`, `auditor`, `cashier`.

### Nama permission

Format: `{modul}.{tindakan}` — `view`, `manage`, `approve`, `reports`, `execute`, `configure`, `audit`.

### Endpoint awam mesti ada had kadar

```php
Route::post('tickets', [...])->middleware('throttle:5,10');
```

## Biometrik (BAB 56C.6A)

### Pagar wajib — semua atau tiada

Face recognition **tidak boleh aktif** sehingga semua dipenuhi:

1. `alternative_method` (rfid/pin/qr/manual)
2. `dpo_name` + `dpo_email` sah
3. `privacy_notice`
4. `consent_version`
5. `retention_days` 1–365
6. `legal_review_date` ≤ 12 bulan

Setiap satu tiada → `422`.

### Consent pekerja ≠ pendaftaran HR

Hubungan majikan–pekerja tidak setara, jadi persetujuan tidak dianggap "bebas" di bawah PDPA Seksyen 6. Mesti ada:

- Borang berasingan
- **Alternatif bukan-biometrik** (tanpa ini consent dianggap dipaksa)
- Notis PDPA bertulis
- Hak tarik balik → embedding dipadam ≤ 24 jam

### Pemadaman

| Data | Bila tarik balik / berhenti kerja |
|---|---|
| Embedding wajah | **Padam serta-merta** |
| Rekod kehadiran (tarikh/masa) | **Kekal** 6 tahun (Akta Kerja 1955) |

### Jangan sekali-kali

- Pemantauan produktiviti pekerja melalui CCTV
- `face_match` melalui laluan ingest biasa (ditolak 422)
- Perkongsian embedding dengan pihak ketiga tanpa consent baharu

## AI Safety (BAB 62)

### Susunan dalam `GatewayService::call()`

```
0. PromptGuard::imbasMesej()      ← MESTI di sini, sebelum feature flag
1. Feature flag (simulasi/live)
2. routeModel
3. Circuit breaker
4. PiiRedactor
5. Anggaran kos
...
```

Jika imbasan dialihkan ke bawah, mod simulasi akan memintasnya.

### PromptGuard ialah lapisan pertama sahaja

Ia mengesan corak jelas. **Bukan** pengganti:

- Tool allowlist + permission check pada setiap panggilan
- Pengasingan konteks tenant
- Pengesahan output sebelum tindakan

### Input luaran dibungkus sebagai DATA

```php
$guard->bungkusSebagaiData($kandunganDokumen, 'DOKUMEN');
```

### Sanitasi output

Kunci provider (`asai_sk_...`), JWT, private key disunting sebelum sampai ke klien.

## Audit

### Perkara yang mesti direkod

| Tindakan | Log |
|---|---|
| Cipta/kemas kini/padam rekod | `AuditLogger::log()` |
| Eksport data | Wajib — termasuk checksum |
| Tontonan rakaman CCTV | `cctv_view_logs` |
| Tindakan biometrik | `biometric_access_logs` |
| Permintaan AI disekat | `Log::warning` |

### Audit log tidak boleh diubah

`activity_log` dilindungi daripada update/delete. Ujian mengesahkannya.

### Jangan log rahsia

Tiada password, API key, token, No. KP penuh dalam audit.

## Kredensial

| Rahsia | Lokasi | Peraturan |
|---|---|---|
| `AI_GATEWAY_KEY` | `.env` | Admin sahaja, tidak pernah ke frontend |
| `VISION_WEBHOOK_SECRET` | `.env` | HMAC-SHA256 wajib |
| RTSP camera | DB, `Crypt::encryptString()` | Tidak dipulangkan API |
| DB password | `.env` | Tidak dalam respons `/ready` |

### Ujian wajib

```php
$this->assertStringNotContainsString('asai_sk_', $json);
$this->assertStringNotContainsString(env('AI_GATEWAY_KEY'), $json);
```

## Senarai Semak Keselamatan

```
[ ] Ujian isolasi tenant untuk setiap endpoint senarai
[ ] assertNotFound() untuk akses by-ID tenant lain
[ ] RBAC diuji untuk setiap kumpulan endpoint
[ ] Rahsia tidak dalam respons API
[ ] Endpoint awam ada throttle
[ ] Audit log untuk tindakan sensitif
[ ] TenantContext, bukan tenant_id dari klien
[ ] Biometrik: pagar disemak sebelum aktif
[ ] AI: PromptGuard di langkah 0
```
