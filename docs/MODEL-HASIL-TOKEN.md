# MODEL HASIL AI BIJAN — Langganan + Token AI

> **Dikemas kini:** 27 Oktober 2026 · **Status:** dikodkan (`app/Modules/Billing/`)
> **Harga pembekal & jualan: DISAHKAAN** daripada kontrak sebenar.
> **Pembetulan penting:** dokumen lama mendakwa "kos sebenar RM75–230/pelanggan".
> **Dakwaan itu salah dan telah dibuang.** Token AI ialah **hasil tambahan**,
> bukan kos.

---

## 1. Model Sebenar

Pelanggan membayar **dua perkara** setiap bulan, dan **kedua-duanya wajib**:

| Item | Harga | Wajib? | Reseller dapat? |
|---|---|---|---|
| Langganan AI BIJAN | **RM388** | ✅ Ya | **RM100** |
| Pakej token AI (dipilih) | **RM55 – RM1,100** | ✅ Ya | ❌ Tidak |
| **Jumlah** | **RM443 – RM1,488** | | RM100 |

> **Kenapa token wajib?** Tanpa token, ciri AI tidak berfungsi. Menjual
> langganan sahaja bermakna pelanggan membayar untuk sistem yang sebahagian
> besarnya mati — dan mereka akan churn pada bulan pertama.

---

## 2. Aliran Wang

```
RM388  langganan
   ├── RM100  → reseller   (TETAP, tidak berkait token)
   └── RM288  → platform

RM55–1,100  token
   └── 100%   → platform   (reseller TIDAK dapat komisen)
```

### Peraturan kritikal

> **Komisen dikira dari LANGGANAN SAHAJA (RM388), bukan jumlah termasuk token.**

Jika dikira dari jumlah penuh:
- Pelanggan beli token RM1,100 → jumlah RM1,488
- 50% had = RM744
- Komisen RM100 masih di bawah had → kelihatan "selamat"
- **Tetapi** jika suatu hari anda naikkan komisen kepada RM200, had 50% akan
  membenarkan RM200 daripada RM1,488 — dan itu memakan margin token.

Dengan peraturan ini, **komisen sentiasa RM100** tidak kira berapa token
pelanggan beli. Diuji dalam `BillingResellerTest::test_komisen_tetap_walau_pelanggan_beli_token_besar`.

---

## 3. Pakej Token — HARGA DISAHKAAN

| Pakej | Token diberi | Harga jual | Kos pembekal | Untung | Margin |
|---|---|---|---|---|---|
| `t60m` | 60 juta | **RM55** | RM5 | **RM50** | **90.9%** |
| `t130m` | 130 juta | **RM88** | RM8 | **RM80** | **90.9%** |
| `t180m` | 180 juta | **RM110** | RM10 | **RM100** | **90.9%** |
| `t1b` | 1 bilion | **RM550** | RM50 | **RM500** | **90.9%** |
| `t2b` | 2 bilion | **RM1,100** | RM100 | **RM1,000** | **90.9%** |

Semua pakej sah **30 hari** (sama seperti pembekal).

### Pakej pembekal yang digunakan

| Pakej jual | Dibeli dari pakej pembekal | Buffer |
|---|---|---|
| `t60m` (60 juta) | 80 juta @ RM5 | 20 juta |
| `t130m` (130 juta) | 140 juta @ RM8 | 10 juta |
| `t180m` (180 juta) | 200 juta @ RM10 | 20 juta |
| `t1b` (1 bilion) | 1.1 bilion @ RM50 | 100 juta |
| `t2b` (2 bilion) | 2.25 bilion @ RM100 | 250 juta |

> **Penting:** pakej pelanggan mesti dibeli dalam pakej pembekal yang **cukup
> besar**. Baki (buffer) penting jika pelanggan guna melebihi pakej — tanpa
> buffer, setiap token tambahan_kwargs dibeli terus daripada anda.

Boleh diubah tanpa deploy: `TOKEN_PACKAGE_60M`, `TOKEN_COST_60M`, dsb. dalam `.env`.

---

## 4. Contoh Pendapatan

### Pelanggan minimum (pakej 60 juta)

| | Bulanan | Tahun 1 |
|---|---|---|
| Pelanggan bayar | RM443 | RM5,316 |
| Reseller dapat | RM100 | RM1,200 |
| Kos token (pembekal) | RM5 | RM60 |
| **Platform terima** | **RM338** | **RM4,056** |

### Pelanggan sederhana (pakej 130 juta)

| | Bulanan | Tahun 1 |
|---|---|---|
| Pelanggan bayar | RM476 | RM5,712 |
| Reseller dapat | RM100 | RM1,200 |
| Kos token | RM8 | RM96 |
| **Platform terima** | **RM368** | **RM4,416** |

### Pelanggan besar (pakej 2 bilion)

| | Bulanan | Tahun 1 |
|---|---|---|
| Pelanggan bayar | RM1,488 | RM17,856 |
| Reseller dapat | RM100 | RM1,200 |
| Kos token | RM100 | RM1,200 |
| **Platform terima** | **RM1,288** | **RM15,456** |

> **Nota:** pakej lebih besar = hasil lebih tinggi, DAN kos pembekal hanya
> ~9% daripada yang dijual. Margin token 90.9% seragam — reka bentuk harga
> yang sangat baik.

---

## 5. Kos Platform — SUDAH DIKIRA untuk token

### 5.1 Margin token — DISAHKAN

| Pakej | Jual | Beli | Untung | Margin | Kos/juta | Untung/juta |
|---|---|---|---|---|---|---|
| `t60m` | RM55 | RM5 | RM50 | 90.9% | RM0.0833 | RM0.8333 |
| `t130m` | RM88 | RM8 | RM80 | 90.9% | RM0.0615 | RM0.6154 |
| `t180m` | RM110 | RM10 | RM100 | 90.9% | RM0.0556 | RM0.5556 |
| `t1b` | RM550 | RM50 | RM500 | 90.9% | RM0.0500 | RM0.5000 |
| `t2b` | RM1,100 | RM100 | RM1,000 | 90.9% | RM0.0500 | RM0.5000 |

> **Margin 90.9% pada semua pakej** — reka bentuk harga yang konsisten.
> Periksa automatik: `BillingService::pakejRugi()` (ada ujian yang gagal
> jika mana-mana pakej jadi rugi atau margin jatuh bawah 50%).

### 5.2 Komponen kos lain

| Komponen | Status |
|---|---|
| Kos token (pembekal) | ✅ **DISAHKAN** — RM5–RM100 setiap pakej |
| Hosting/server | **Tidak ditanggung** (menurut pengguna) |
| Yuran gateway CHIP | Perlu semak kontrak (~1–3% daripada transaksi) |
| Masa sokongan | Tidak dikira dalam wang |

**Untuk pelanggan minimum RM443**, yuran CHIP 2% = RM8.86/bulan.

### 5.3 Yang masih perlu disahkan

- [ ] **Kadar yuran CHIP sebenar** daripada kontrak
- [ ] **Penggunaan sebenar** — pakej dijual sebagai had maksimum. Jika pelanggan
      guna 20%, margin anda lebih baik; jika 100%, dapat margin paling nipis.
      Perlu dashboard kos AI per pelanggan daripada `ai_usage_events`.

---

## 6. Risiko Yang Masih Nyata

| Risiko | Kesan | Mitigasi |
|---|---|---|
| **Harga pembekal naik** | Margin token terhakis serta-merta | Semak kontrak; naikkan harga pakej untuk pelanggan baharu |
| **Pelanggan guna 100% token** | Margin paling nipis | Ukur penggunaan sebenar; laraskan harga pakej |
| **Churn selepas 30 hari** | RM100 sudah jadi hak, pelanggan berhenti bulan ke-2 | Kaji semula selepas 50 pelanggan |
| **Liabiliti komisen** | 100 × RM100 × 12 = **RM120,000** | `RESELLER_COMMISSION_MONTHS=24` |
| **Reseller berhenti kerja** | Tetap bayar jika pelanggan langgan | Terma: komisen tamat jika reseller ditamatkan |

---

## 7. Yang Sudah Dikodkan

| Ciri | Lokasi | Ujian |
|---|---|---|
| Pakej token wajib | `BillingService::ciptaInvoisLangganan()` | `test_api_langgan_tanpa_token_ditolak` |
| Jumlah = langganan + token | `BillingService::jumlahBulanan()` | `test_jumlah_bulanan_langganan_plus_token` |
| Komisen dari langganan sahaja | `ResellerCommissionService` | `test_komisen_tetap_walau_pelanggan_beli_token_besar` |
| Had 50% dari langganan | `BillingService::komisenUntuk()` | `test_komisen_tidak_melebihi_langganan` |
| 12 peraturan reseller | `ResellerCommissionService` | 32 ujian |
| Webhook CHIP selamat | `ChipWebhookService` | 6 pagar diuji |

---

## 8. Tindakan Diperlukan Sebelum Lancar

- [x] ~~Sahkan harga pembekal token~~ ✅ **DISAHKAN 27 Okt 2026**
- [x] ~~Kira margin setiap pakej~~ ✅ **90.9% seragam — semua safely**
- [ ] **Semak kadar yuran CHIP** daripada kontrak (~1–3%)
- [ ] **Bina dashboard kos AI per pelanggan** daripada `ai_usage_events`
- [ ] Daftar webhook CHIP: `/api/v1/webhooks/chip`
- [ ] Set `CHIP_API_KEY`, `CHIP_BRAND_ID`, `CHIP_WEBHOOK_PUBLIC_KEY`
- [ ] Putuskan merchant of record (terima dana sendiri vs rakan kongsi)
- [ ] Semak semula selepas 20 pelanggan pertama dengan data penggunaan sebenar

⚠️ **Margin token selamat (90.9%) tetapi yuran CHIP masih belum disahkan.**
Pada pakej minimum RM443, yuran 3% = RM13.29 — masih margin positif, tetapi
perlu disahkan sebelum pincang.
