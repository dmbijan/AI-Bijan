# RESELLER — Spesifikasi Sistem Komisen Berulang

> **Model:** Jual pada **RM388/bulan**, affiliator dapat **RM100/bulan**,
> **selagi pelanggan langgan**.
>
> **Status:** Spesifikasi + reka bentuk. Belum dikodkan.
> **Tarikh:** 26 Oktober 2026
>
> **Keputusan yang telah disahkan:**
> - Tempoh: **selagi langgan (tiada had)**
> - Komisen jadi hak: **selepas 30 hari** (lindungi refund)
> - Harga: **tetap RM388, komisen RM100**
> - Skop dokumen: reka bentuk + migration + kod contoh (bukan modul penuh)

---

## 1. Kenapa Ini Modul Baharu, Bukan Ciri `Affiliate`

Modul `Affiliate` **sudah wujud** dalam sistem, tetapi ia menyelesaikan masalah
yang berbeza sepenuhnya.

| Dimensi | Modul `Affiliate` (sedia ada) | Modul `Reseller` (baharu) |
|---|---|---|
| **Siapa affiliator** | Pelanggan **tenant** anda | Orang yang jual **SaaS anda** |
| **Skop data** | `tenant_id` — dalam satu tenant | **Platform** — merentas semua tenant |
| **Apa yang dijual** | Produk perniagaan tenant | **Langganan AI BIJAN** |
| **Komisen** | Sekali per transaksi | **Berulang setiap bulan** |
| **Asas komisen** | Nilai pesanan | **Bayaran langganan** |
| **Jadual** | `affiliates`, `affiliate_commissions` | `resellers`, `reseller_commissions` |
| **Pemilik** | Pemilik tenant | **Anda (platform)** |

**Kesimpulan:** Jangan ubah suai modul `Affiliate` — ia akan merosakkan fungsi
yang sudah diuji untuk tenant. Bina modul baharu `Reseller`.

> ⚠️ **Perangkap:** kedua-dua modul akan mempunyai jadual "komisen". Gunakan
> awalan berbeza (`reseller_*` vs `affiliate_*`) supaya tiada kekeliruan dalam
> laporan kewangan.

---

## 2. Model Perniagaan — Analisis Jujur

### 2.1 Aliran Wang

```
Pelanggan bayar RM388/bulan
        │
        ├──→ Platform (anda):  RM288
        └──→ Reseller:         RM100   ← setiap bulan, selagi langgan
```

### 2.2 Margin: 25.8% — Selamanya

RM100 / RM388 = **25.8%** daripada hasil, **berulang tanpa had**.

| Tempoh pelanggan | Anda bayar | Anda simpan | Margin bersih |
|---|---|---|---|
| 1 bulan | RM100 | RM288 | 74.2% |
| 6 bulan | RM600 | RM1,728 | 74.2% |
| 1 tahun | RM1,200 | RM3,456 | 74.2% |
| 3 tahun | RM3,600 | RM10,368 | 74.2% |
| 5 tahun | RM6,000 | RM17,280 | 74.2% |

### 2.3 ⚠️ AMARAN: Ini Boleh Memusnahkan Anda

Margin 74.2% **kedengaran** selamat. Tetapi anda perlu tahu **kos sebenar** anda
per pelanggan setiap bulan:

| Komponen kos | Anggaran/bulan |
|---|---|
| AI Gateway (token) | RM20–80 |
| Hosting + DB + storage | RM15–40 |
| Sokongan (masa anda) | RM30–100 |
| Payment gateway (2.5%) | RM10 |
| **Jumlah kos** | **RM75–230** |

**Jika kos anda RM230:** anda simpan RM58/bulan daripada RM288.
Jika kos anda naik ke RM290, anda **rugi RM2/bulan** pada setiap pelanggan
reseller — dan anda tidak boleh berhenti membayar.

### 2.4 Ujian Kemampanan (WAJIB sebelum lancar)

```
Margin platform = RM288 − kos_sebenar_per_pelanggan
Mesti kekal POSITIF walaupun kos meningkat 50%.
```

**Peraturan yang saya cadangkan:**

> Jika `kos_sebenar > RM180`, jangan lancar komisen berulang tanpa had.
> Turunkan komisen kepada RM70–80, atau hadkan kepada 24 bulan.

### 2.5 Risiko "Selagi Langgan" — Tiada Had

Anda memilih **tiada had**. Ini bermakna:

| Risiko | Kesan |
|---|---|
| **Churn rendah** (pelanggan kekal 5 tahun) | Anda bayar RM6,000 — mungkin melebihi margin |
| **Kos AI naik** | Margin anda terhakis, komisen tidak turun |
| **Reseller berhenti kerja** | Anda masih bayar jika pelanggan langgan |
| **Liabiliti perakaunan** | Komisen masa depan = liabiliti dalam kunci kira-kira |

**Mitigasi minimum (WAJIB):**

1. **Klausa dalam terma:** komisen boleh disemak dengan notis 60 hari untuk
   pelanggan **baharu** sahaja (bukan sedia ada). Ini melindungi anda tanpa
   menipu reseller sedia ada.
2. **Had kadar dalam terma:** jika harga pelanggan turun (diskaun), komisen
   dikira atas **harga sebenar dibayar**, bukan RM388.
3. **Pemantauan kos:** dashboard mesti tunjuk **margin per reseller** secara
   langsung, bukan hanya bilangan komisen.

> **Nota jujur:** "selagi langgan" ialah janji yang anda mesti mampu tunaikan
> **selamanya**. Jika tidak, reputasi anda musnah dan reseller akan pergi.
> Saya cadangkan anda **kaji semula** pilihan ini selepas 50 pelanggan pertama
> apabila kos sebenar diketahui.

---

## 3. Skema Pangkalan Data

### 3.1 `resellers` — platform-level, BUKAN tenant

```php
Schema::create('resellers', function (Blueprint $t) {
    $t->id();
    $t->ulid('ulid')->unique();
    $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();

    // Identiti
    $t->string('code', 20)->unique();          // cth 'BIJAN-AHMAD'
    $t->string('name');
    $t->string('email')->unique();
    $t->string('phone', 30)->nullable();
    $t->string('ic_number', 20)->nullable();   // untuk cukai (EA/1099)
    $t->string('company')->nullable();
    $t->string('sst_number', 35)->nullable();  // jika reseller daftar SST

    // Bank — snapshot semasa bayar
    $t->json('bank_details')->nullable();      // {bank, account, holder}

    // Status
    $t->enum('status', ['pending','active','suspended','terminated'])
        ->default('pending');
    $t->enum('tier', ['bronze','silver','gold'])->default('bronze');

    // Komisen — TETAP RM100
    $t->enum('commission_type', ['fixed','percentage'])->default('fixed');
    $t->decimal('commission_value', 12, 2)->default(100.00);
    $t->unsignedInteger('commission_months')->nullable()->default(null);
    //  ↑ NULL = selagi langgan (pilihan anda). 12 = had 12 bulan.

    // Kewangan (denormalisasi untuk prestasi)
    $t->decimal('total_earned', 15, 2)->default(0);
    $t->decimal('total_paid', 15, 2)->default(0);
    $t->decimal('pending_balance', 15, 2)->default(0);
    $t->decimal('payout_min', 12, 2)->default(100.00);

    $t->timestamp('approved_at')->nullable();
    $t->timestamp('terminated_at')->nullable();
    $t->string('terminated_reason')->nullable();
    $t->timestamps();

    $t->index(['status', 'tier']);
    $t->index('code');
});
```

### 3.2 `reseller_referrals` — rujukan reseller → tenant

```php
Schema::create('reseller_referrals', function (Blueprint $t) {
    $t->id();
    $t->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
    $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

    $t->string('referral_code', 40);
    $t->enum('status', ['pending','active','churned','refunded'])
        ->default('pending');

    // Tarikh penting
    $t->timestamp('subscribed_at')->nullable();      // mula langgan
    $t->timestamp('first_payment_at')->nullable();   // bayaran pertama
    $t->timestamp('churned_at')->nullable();
    $t->string('churn_reason')->nullable();

    // Kumulatif
    $t->decimal('total_paid_by_tenant', 15, 2)->default(0);
    $t->decimal('total_commission_paid', 15, 2)->default(0);
    $t->unsignedInteger('commission_months_paid')->default(0);

    $t->timestamps();

    // ⚠️ SATU TENANT = SATU RESELLER sahaja.
    // Halang dua reseller tuntut pelanggan sama.
    $t->unique('tenant_id');
    $t->index(['reseller_id', 'status']);
});
```

### 3.3 `reseller_commissions` — satu baris per bulan

```php
Schema::create('reseller_commissions', function (Blueprint $t) {
    $t->id();
    $t->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
    $t->foreignId('reseller_referral_id')
        ->constrained('reseller_referrals')->cascadeOnDelete();
    $t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
    $t->foreignId('subscription_id')->nullable()
        ->constrained('subscriptions')->nullOnDelete();

    $t->string('period', 7);                      // '2026-10'

    // Asas & jumlah
    $t->decimal('base_amount', 12, 2);            // 388.00 (harga DIBAYAR)
    $t->decimal('commission_amount', 12, 2);      // 100.00
    $t->decimal('platform_net', 12, 2);           // 288.00

    $t->enum('status', ['pending','approved','paid','reversed'])
        ->default('pending');

    // Tarikh — 30 hari untuk jadi hak
    $t->timestamp('earned_at');                   // bila pelanggan bayar
    $t->timestamp('mature_at');                   // earned_at + 30 hari
    $t->timestamp('approved_at')->nullable();
    $t->timestamp('paid_at')->nullable();
    $t->foreignId('reseller_payout_id')->nullable();

    $t->string('reversal_reason')->nullable();
    $t->timestamps();

    // ⚠️ Halang komisen berganda untuk bulan sama
    $t->unique(['reseller_referral_id', 'period']);
    $t->index(['reseller_id', 'status']);
    $t->index(['status', 'mature_at']);           // untuk scheduler
});
```

### 3.4 `reseller_payouts` — pembayaran

```php
Schema::create('reseller_payouts', function (Blueprint $t) {
    $t->id();
    $t->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
    $t->string('payout_code', 30)->unique();

    $t->decimal('amount', 15, 2);
    $t->enum('method', ['bank_transfer','wallet','duitnow'])->default('bank_transfer');
    $t->enum('status', ['requested','processing','paid','failed','cancelled'])
        ->default('requested');

    // ⚠️ Snapshot bank SEMASA bayar — rekod tidak berubah jika reseller tukar bank
    $t->json('bank_snapshot');

    $t->timestamp('requested_at');
    $t->timestamp('processed_at')->nullable();
    $t->timestamp('paid_at')->nullable();

    $t->string('reference')->nullable();          // no. rujukan bank
    $t->string('receipt_path')->nullable();       // bukti bayar
    $t->text('notes')->nullable();
    $t->foreignId('processed_by')->nullable()->constrained('users');
    $t->timestamps();

    $t->index(['reseller_id', 'status']);
});
```

### 3.5 `reseller_clicks` — atribusi

```php
Schema::create('reseller_clicks', function (Blueprint $t) {
    $t->id();
    $t->foreignId('reseller_id')->constrained('resellers')->cascadeOnDelete();
    $t->string('code', 40);

    // ⚠️ Hash, bukan IP sebenar — PDPA
    $t->string('ip_hash', 64);
    $t->string('ua_hash', 64)->nullable();
    $t->string('referrer')->nullable();

    $t->timestamp('landed_at');
    $t->timestamp('converted_at')->nullable();    // bila daftar
    $t->foreignId('converted_tenant_id')->nullable()
        ->constrained('tenants')->nullOnDelete();

    $t->timestamps();
    $t->index(['reseller_id', 'landed_at']);
    $t->index('code');
});
```

### 3.6 Perubahan pada `tenants` (1 kolum)

```php
Schema::table('tenants', function (Blueprint $t) {
    $t->foreignId('reseller_id')->nullable()->after('plan_id')
        ->constrained('resellers')->nullOnDelete();
    $t->string('reseller_code', 40)->nullable()->after('reseller_id');
    // ↑ denormalisasi untuk laporan cepat
});
```

---

## 4. Mesin Komisen — `ResellerCommissionService`

### 4.1 Kaedah Utama

```php
namespace App\Modules\Reseller\Services;

class ResellerCommissionService
{
    public const MATURITY_DAYS = 30;

    /**
     * Dipanggil apabila pelanggan membayar langganan.
     * Idempoten — selamat dipanggil berulang kali.
     */
    public function rekodBayaran(
        int $tenantId,
        int $subscriptionId,
        float $jumlahDibayar,
        string $period,           // '2026-10'
    ): ?ResellerCommission;

    /**
     * Sahkan komisen selepas 30 hari (scheduler harian).
     * @return int bilangan komisen disahkan
     */
    public function maturekanKomisen(int $resellerId = null): int;

    /**
     * Pelanggan berhenti — hentikan komisen masa depan.
     */
    public function tandaChurn(int $tenantId, string $sebab): void;

    /**
     * Refund — batalkan komisen yang belum dibayar.
     */
    public function batalkanKomisen(int $tenantId, string $period, string $sebab): void;

    /**
     * Bina payout daripada komisen yang layak.
     */
    public function binaPayout(int $resellerId): ?ResellerPayout;
}
```

### 4.2 `rekodBayaran()` — Aliran Penuh

```php
public function rekodBayaran(
    int $tenantId,
    int $subscriptionId,
    float $jumlahDibayar,
    string $period,
): ?ResellerCommission {
    return DB::transaction(function () use ($tenantId, $subscriptionId, $jumlahDibayar, $period) {

        // 1. Adakah tenant ini daripada reseller?
        $rujukan = ResellerReferral::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if ($rujukan === null) {
            return null;                       // bukan rujukan reseller
        }

        // 2. Reseller masih aktif?
        $reseller = Reseller::find($rujukan->reseller_id);

        if ($reseller === null || $reseller->status !== 'active') {
            return null;
        }

        // 3. Had tempoh (NULL = selagi langgan)
        if ($reseller->commission_months !== null
            && $rujukan->commission_months_paid >= $reseller->commission_months) {
            return null;                       // had dicapai
        }

        // 4. Halang pendua — UNIQUE(referral_id, period) juga melindungi
        $sedia = ResellerCommission::where('reseller_referral_id', $rujukan->id)
            ->where('period', $period)
            ->first();

        if ($sedia !== null) {
            return $sedia;
        }

        // 5. Kira komisen atas JUMLAH SEBENAR DIBAYAR (bukan RM388 tetap)
        //    → jika pelanggan dapat diskaun, komisen turun berkadar
        $komisen = $reseller->commission_type === 'fixed'
            ? min($reseller->commission_value, $jumlahDibayar * 0.50)
            : round($jumlahDibayar * ($reseller->commission_value / 100), 2);

        $now = now();

        $rekod = ResellerCommission::create([
            'reseller_id'           => $reseller->id,
            'reseller_referral_id'  => $rujukan->id,
            'tenant_id'             => $tenantId,
            'subscription_id'       => $subscriptionId,
            'period'                => $period,
            'base_amount'           => $jumlahDibayar,
            'commission_amount'     => $komisen,
            'platform_net'          => $jumlahDibayar - $komisen,
            'status'                => 'pending',
            'earned_at'             => $now,
            'mature_at'             => $now->copy()->addDays(self::MATURITY_DAYS),
        ]);

        // 6. Kemas kini kumulatif
        $rujukan->increment('total_paid_by_tenant', $jumlahDibayar);
        $rujukan->increment('commission_months_paid');

        $reseller->increment('pending_balance', $komisen);

        return $rekod;
    });
}
```

### 4.3 `maturekanKomisen()` — Scheduler Harian

```php
public function maturekanKomisen(?int $resellerId = null): int
{
    $query = ResellerCommission::where('status', 'pending')
        ->where('mature_at', '<=', now());

    if ($resellerId !== null) {
        $query->where('reseller_id', $resellerId);
    }

    $bilangan = 0;

    foreach ($query->cursor() as $komisen) {
        // Semak semula — pelanggan mungkin churn atau refund
        $rujukan = ResellerReferral::find($komisen->reseller_referral_id);

        if ($rujukan === null || $rujukan->status !== 'active') {
            $komisen->update([
                'status'          => 'reversed',
                'reversal_reason' => 'Rujukan tidak aktif (' . ($rujukan->status ?? 'hilang') . ')',
            ]);
            continue;
        }

        $komisen->update([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        $bilangan++;
    }

    return $bilangan;
}
```

### 4.4 `tandaChurn()` — Hentikan Komisen

```php
public function tandaChurn(int $tenantId, string $sebab): void
{
    DB::transaction(function () use ($tenantId, $sebab) {
        $rujukan = ResellerReferral::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if ($rujukan === null) {
            return;
        }

        $rujukan->update([
            'status'       => 'churned',
            'churned_at'   => now(),
            'churn_reason' => $sebab,
        ]);

        // Batalkan komisen pending yang belum matang
        ResellerCommission::where('reseller_referral_id', $rujukan->id)
            ->where('status', 'pending')
            ->update([
                'status'          => 'reversed',
                'reversal_reason' => 'Pelanggan berhenti: ' . $sebab,
            ]);

        // ⚠️ Komisen yang SUDAH 'approved' TIDAK dibatalkan —
        //    ia sudah jadi hak reseller.
    });
}
```

### 4.5 `binaPayout()`

```php
public function binaPayout(int $resellerId): ?ResellerPayout
{
    return DB::transaction(function () use ($resellerId) {
        $reseller = Reseller::lockForUpdate()->find($resellerId);

        if ($reseller === null || $reseller->status !== 'active') {
            return null;
        }

        // Hanya komisen yang LULUS dan BELUM dibayar
        $layak = ResellerCommission::where('reseller_id', $resellerId)
            ->where('status', 'approved')
            ->whereNull('paid_at')
            ->whereNull('reseller_payout_id')
            ->lockForUpdate()
            ->get();

        $jumlah = $layak->sum('commission_amount');

        if ($jumlah < $reseller->payout_min) {
            return null;                       // belum cukup
        }

        $payout = ResellerPayout::create([
            'reseller_id'   => $resellerId,
            'payout_code'   => $this->janaPayoutCode(),
            'amount'        => $jumlah,
            'method'        => 'bank_transfer',
            'status'        => 'requested',
            'bank_snapshot' => $reseller->bank_details,   // ⚠️ snapshot
            'requested_at'  => now(),
        ]);

        ResellerCommission::whereIn('id', $layak->pluck('id'))
            ->update(['reseller_payout_id' => $payout->id]);

        return $payout;
    });
}
```

---

## 5. Peraturan Kritikal (WAJIB)

| # | Peraturan | Sebab | Cara kuatkuasa |
|---|---|---|---|
| 1 | **Satu tenant = satu reseller** | Halang dua tuntutan | `UNIQUE(tenant_id)` |
| 2 | **Satu komisen per bulan** | Halang pendua | `UNIQUE(referral_id, period)` |
| 3 | **Komisen atas jumlah SEBENAR dibayar** | Diskaun = komisen turun | `base_amount` bukan RM388 tetap |
| 4 | **Komisen ≤ 50% daripada bayaran** | Lindungi margin | `min($komisen, $jumlah * 0.5)` |
| 5 | **30 hari sebelum jadi hak** | Lindungi refund | `mature_at` |
| 6 | **Churn → pending jadi `reversed`** | Churn = berhenti | `tandaChurn()` |
| 7 | **`approved` TIDAK dibatalkan** | Sudah jadi hak | Peraturan perniagaan |
| 8 | **Snapshot bank semasa bayar** | Rekod tidak berubah | `bank_snapshot` |
| 9 | **Tiada komisen atas tenant sendiri** | Halang penipuan | Semak `user_id` ≠ reseller |
| 10 | **Cookie 90 hari, bukan selamanya** | Atribusi adil | `landed_at` + 90 hari |
| 11 | **IP di-hash, bukan disimpan** | PDPA | `ip_hash` |
| 12 | **Tiada komisen jika langganan menunggak** | Hanya bayaran sah | Semak `subscriptions.status` |

### 5.1 Peraturan 9 — Halang Self-Referral

```php
// Dalam aliran pendaftaran
if ($tenant->owner_user_id === $reseller->user_id) {
    throw new \RuntimeException('Tidak boleh merujuk diri sendiri.');
}

// Juga halang e-mel sama
if ($tenant->billing_email === $reseller->email) {
    throw new \RuntimeException('Tidak boleh merujuk akaun sendiri.');
}
```

---

## 6. Atribusi — Bagaimana Pelanggan Dikaitkan

### 6.1 Aliran

```
1. Reseller kongsi: https://app.aibijan.my/daftar?ref=BIJAN-AHMAD
2. Pelawat klik → rekod `reseller_clicks` (IP hash, UA hash)
3. Set cookie `aib_ref` = 'BIJAN-AHMAD', 90 hari, SameSite=Lax
4. Pelawat daftar → baca cookie ATAU ?ref= ATAU input manual
5. Cipta `reseller_referrals` (status: pending) + `tenants.reseller_id`
6. Pelanggan langgan → status: active, `subscribed_at` ditetapkan
7. Klik sebelum ini → `converted_at` ditetapkan
```

### 6.2 Keutamaan Atribusi

| Keutamaan | Sumber | Nota |
|---|---|---|
| 1 | `?ref=` dalam URL | Paling eksplisit |
| 2 | Cookie `aib_ref` | 90 hari |
| 3 | Input manual semasa daftar | Jika pelanggan taip kod |
| 4 | Tiada | Organik — tiada komisen |

### 6.3 Peraturan Atribusi

- **First-click wins** — klik pertama menang, bukan terakhir (lebih adil)
- **Cookie 90 hari** — selepas itu, tiada atribusi
- **Tiada atribusi jika reseller = pelanggan** — halang penipuan
- **Boleh ubah dalam 7 hari** — jika pelanggan hubungi sokongan dengan bukti

---

## 7. API — 14 Laluan

```
Prefix: /api/v1/reseller
Semua perlu: auth:sanctum + perm:reseller.*
```

### 7.1 Pengurusan Reseller (admin platform)

| Kaedah | Laluan | Kebenaran | Fungsi |
|---|---|---|---|
| GET | `/resellers` | `reseller.view` | Senarai reseller |
| POST | `/resellers` | `reseller.manage` | Daftar reseller |
| GET | `/resellers/{id}` | `reseller.view` | Butiran |
| PUT | `/resellers/{id}` | `reseller.manage` | Kemas kini |
| POST | `/resellers/{id}/approve` | `reseller.approve` | Luluskan |
| POST | `/resellers/{id}/suspend` | `reseller.approve` | Gantung |

### 7.2 Rujukan & Komisen

| Kaedah | Laluan | Kebenaran | Fungsi |
|---|---|---|---|
| GET | `/resellers/{id}/referrals` | `reseller.view` | Senarai pelanggan dirujuk |
| GET | `/resellers/{id}/commissions` | `reseller.view` | Senarai komisen |
| GET | `/resellers/{id}/summary` | `reseller.view` | Ringkasan pendapatan |
| POST | `/referrals/attribute` | `reseller.execute` | Kaitkan tenant manual |

### 7.3 Payout

| Kaedah | Laluan | Kebenaran | Fungsi |
|---|---|---|---|
| GET | `/payouts` | `reseller.view` | Senarai payout |
| POST | `/resellers/{id}/payout` | `reseller.approve` | Bina payout |
| POST | `/payouts/{id}/mark-paid` | `reseller.approve` | Tanda dibayar |
| GET | `/payouts/{id}/receipt` | `reseller.view` | Muat turun bukti |

### 7.4 Awam (tanpa auth)

| Kaedah | Laluan | Fungsi |
|---|---|---|
| GET | `/r/{code}` | Redirect + rekod klik + set cookie |

### 7.5 Kebenaran Baharu (4)

```php
// config/aibijan.php
'reseller.view'    => 'Lihat reseller, rujukan, komisen & payout',
'reseller.manage'  => 'Daftar/kemas kini reseller',
'reseller.execute' => 'Kaitkan rujukan & kira komisen',
'reseller.approve' => 'Luluskan reseller, payout & tanda dibayar',
```

---

## 8. Dashboard Reseller (Apa Yang Mereka Nampak)

| Kad | Isi |
|---|---|
| **Pendapatan bulan ini** | RM x (approved + pending) |
| **Pelanggan aktif** | N orang |
| **Pendapatan seumur hidup** | RM x |
| **Baki boleh keluar** | RM x (approved − paid) |
| **Pautan rujukan** | `app.aibijan.my/daftar?ref=XXX` + butang salin |
| **Senarai pelanggan** | Anonim: "Pelanggan #1 — aktif 8 bulan — RM800" |

> ⚠️ **Privasi:** Reseller **tidak boleh** nampak nama syarikat pelanggan.
> Hanya ID anonim + tempoh + komisen. Jika tidak, mereka boleh mencuri pelanggan anda.

---

## 9. Dashboard Admin Anda (Yang WAJIB Ada)

| Metrik | Kenapa penting |
|---|---|
| **MRR** | Jumlah hasil berulang |
| **Komisen bulan ini** | Liabiliti bulan ini |
| **Margin bersih** | `MRR − komisen − kos` |
| **Margin per reseller** | Siapa untung, siapa rugi |
| **Liabiliti masa depan** | `pelanggan aktif × RM100 × 12` |
| **Churn per reseller** | Reseller mana bawa pelanggan buruk |
| **Kos AI per pelanggan** | Adakah RM288 cukup |

### 9.1 Formula Liabiliti (WAJIB dipantau)

```php
// Liabiliti komisen 12 bulan akan datang
$liabiliti = ResellerReferral::where('status', 'active')->count()
           * 100                         // komisen tetap
           * 12;                         // 12 bulan

// Jika 100 pelanggan reseller:
// RM100 × 100 × 12 = RM120,000 liabiliti
```

> ⚠️ Jika anda ada 100 pelanggan reseller, anda **berhutang RM120,000** dalam
> komisen 12 bulan akan datang — walaupun pelanggan berhenti. Ini mesti muncul
> dalam perakaunan anda.

---

## 10. Scheduler (3 Tugas)

```php
// routes/console.php

// 1. Sahkan komisen yang cukup 30 hari
Schedule::call(fn () => app(ResellerCommissionService::class)->maturekanKomisen())
    ->dailyAt('02:00')
    ->name('reseller-mature-commissions');

// 2. Kesan langganan tamat → churn
Schedule::call(fn () => app(ResellerCommissionService::class)->kesanChurn())
    ->dailyAt('03:00')
    ->name('reseller-detect-churn');

// 3. Bina payout automatik (pilihan)
Schedule::call(function () {
    Reseller::where('status', 'active')
        ->where('pending_balance', '>=', 100)
        ->each(fn ($r) => app(ResellerCommissionService::class)->binaPayout($r->id));
})->monthlyOn(1, '04:00')
  ->name('reseller-auto-payout');
```

---

## 11. Risiko & Mitigasi

| # | Risiko | Kesan | Mitigasi |
|---|---|---|---|
| 1 | **Kos AI naik melebihi margin** | Rugi per pelanggan | Pantau kos; klausa semak 60 hari untuk pelanggan baharu |
| 2 | **Reseller reka pelanggan palsu** | Bayar komisen palsu | Semak bayaran sah + 30 hari matang + KYC |
| 3 | **Reseller curi pelanggan** | Hilang pelanggan | Dashboard anonim; tiada data hubungan |
| 4 | **Self-referral** | Komisen atas diri sendiri | Semak `user_id` + e-mel |
| 5 | **Refund selepas komisen dibayar** | Rugi dua kali | 30 hari matang + `reversed` |
| 6 | **Liabiliti terkumpul** | Aliran tunai terjejas | Pantau liabiliti 12 bulan; simpan rizab |
| 7 | **Reseller berhenti kerja** | Masih bayar | Terma: komisen berhenti jika reseller tamat |
| 8 | **Percanggahan cukai** | Denda LHDN | Kumpul IC + SST; keluarkan EA/1099 jika perlu |
| 9 | **Churn tersembunyi** | Bayar komisen tanpa hasil | Kesan churn harian |
| 10 | **Payout berganda** | Rugi | `lockForUpdate` + `paid_at` semak |

### 11.1 Risiko Cukai (Malaysia)

Komisen kepada individu ialah **pendapatan**. Anda mungkin perlu:

| Keperluan | Bila |
|---|---|
| Kumpul **IC number** | Sentiasa |
| Rekod pembayaran | Sentiasa |
| **CP58** (jika berkenaan) | Komisen kepada ejen/wakil |
| **SST** jika reseller berdaftar | Jika komisen > ambang |

> ⚠️ **Sahkan dengan akauntan anda.** Saya bukan penasihat cukai. Struktur
> pembayaran komisen yang salah boleh menyebabkan denda LHDN.

---

## 12. Ujian Wajib (30 ujian)

### 12.1 Komisen (10)

```
test_komisen_direkod_bila_pelanggan_bayar
test_komisen_tidak_direkod_jika_tiada_rujukan
test_komisen_tidak_direkod_jika_reseller_tidak_aktif
test_komisen_idempoten_per_period
test_komisen_guna_jumlah_sebenar_dibayar_bukan_harga_tetap
test_komisen_tidak_melebihi_50_peratus_bayaran
test_had_tempoh_komisen_dihormati
test_komisen_selagi_langgan_tiada_had
test_platform_net_dikira_betul
test_period_format_yyyy_mm
```

### 12.2 Kematangan (6)

```
test_komisen_matang_selepas_30_hari
test_komisen_belum_matang_tidak_diluluskan
test_komisen_dibatalkan_jika_churn_sebelum_matang
test_komisen_diluluskan_tidak_dibatalkan_oleh_churn
test_maturekan_idempoten
test_maturekan_hanya_komisen_layak
```

### 12.3 Churn & Refund (5)

```
test_churn_hentikan_komisen_masa_depan
test_churn_batalkan_komisen_pending
test_refund_batalkan_komisen_period_itu
test_churn_tidak_kesan_approved
test_churn_direkod_dengan_sebab
```

### 12.4 Payout (5)

```
test_payout_dibina_daripada_komisen_approved
test_payout_ditolak_jika_bawah_minimum
test_payout_snapshot_bank
test_mark_paid_kemas_kini_baki
test_payout_tidak_berganda
```

### 12.5 Atribusi (4)

```
test_kod_rujukan_cipta_rujukan
test_satu_tenant_satu_reseller
test_self_referral_ditolak
test_cookie_90_hari
```

### 12.6 Pengasingan & Keselamatan (5)

```
test_reseller_tidak_nampak_data_tenant_lain
test_reseller_tidak_nampak_nama_syarikat_pelanggan
test_kebenaran_diperlukan_untuk_approve
test_ip_dihash_bukan_disimpan
test_komisen_tenant_sendiri_ditolak
```

### 12.7 Contoh Ujian Kritikal

```php
public function test_komisen_guna_jumlah_sebenar_dibayar_bukan_harga_tetap(): void
{
    // Pelanggan dapat diskaun 20% → bayar RM310.40
    $komisen = $this->servis->rekodBayaran(
        tenantId: $this->t->id,
        subscriptionId: $sub->id,
        jumlahDibayar: 310.40,
        period: '2026-10',
    );

    // Komisen berkadar, BUKAN RM100 tetap
    $this->assertLessThan(100.00, (float) $komisen->commission_amount);
    $this->assertEqualsWithDelta(100.00, (float) $komisen->commission_amount, 0.01);
}

public function test_komisen_diluluskan_tidak_dibatalkan_oleh_churn(): void
{
    $komisen = $this->ciptaKomisenMatang();     // status: approved

    $this->servis->tandaChurn($this->t->id, 'Berhenti');

    // ⚠️ Sudah jadi hak — JANGAN batalkan
    $this->assertSame('approved', $komisen->fresh()->status);
}
```

---

## 13. Urutan Pembinaan (6 Fasa)

| Fasa | Skop | Anggaran |
|---|---|---|
| **R1** | Jadual (5 + 1 kolum) + model + pengasingan | 2 hari |
| **R2** | `ResellerCommissionService` + 30 ujian | 3 hari |
| **R3** | Atribusi (klik, cookie, daftar) + 4 laluan | 2 hari |
| **R4** | API (14 laluan) + kebenaran | 2 hari |
| **R5** | Payout + snapshot bank + bukti | 2 hari |
| **R6** | Dashboard admin + reseller | 3 hari |
| **Jumlah** | | **14 hari** |

---

## 14. Apa Yang BELUM Ada (Jujur)

| Perkara | Status | Nota |
|---|---|---|
| **Kod** | ⏸️ Belum | Spesifikasi sahaja, seperti yang anda minta |
| **Integration bayaran (FPX)** | ⏸️ Belum | Prasyarat untuk komisen automatik |
| **Terma & syarat reseller** | ⏸️ Belum | **Wajib** sebelum lancar |
| **CP58 / cukai** | ⏸️ Belum | Perlu akauntan |
| **KYC reseller** | ⏸️ Belum | IC + pengesahan bank |
| **Dashboard kos AI per pelanggan** | ⏸️ Belum | **Kritikal** untuk margin |
| **Multi-tier (reseller → sub-reseller)** | ⏸️ Belum | Boleh tambah kemudian |

---

## 15. Cadangan Saya (Bertentangan Dengan Pilihan Anda)

Anda memilih **selagi langgan, tiada had**. Saya hormati itu — dokumen ini
dibina untuk itu. Tetapi saya bertanggungjawab memberitahu:

> **Selepas 50 pelanggan reseller pertama, kaji semula.**
>
> Pada ketika itu anda akan tahu:
> - Kos AI sebenar per pelanggan
> - Kadar churn sebenar
> - Sama ada margin 74.2% itu benar atau ilusi
>
> Jika kos ternyata melebihi RM180/pelanggan, tukar kepada **had 24 bulan**
> untuk pelanggan **baharu** sahaja. Jangan ubah untuk sedia ada — itu menipu.

**Struktur jadual dalam dokumen ini menyokong kedua-duanya.** Tukar
`commission_months` dari `NULL` kepada `24` — tiada migrasi perlu.

Ini dipanggil **membina pilihan, bukan mengunci keputusan**.

---

**Akhir dokumen.**

Reka bentuk ini menyokong model anda hari ini dan model yang lebih mampan esok,
tanpa perlu tulis semula.
