# Cadangan & Idea untuk AI BIJAN

**Tarikh asal:** 24 Oktober 2026
**Dikemas kini:** 26 Oktober 2026
**Asas:** Analisis kod sebenar — **58 modul, 962 ujian lulus, 1,518 endpoint, 66 resource Filament**

> **Diagnosis teras (asal 24 Okt):** AI BIJAN ada **backend gred produksi** tetapi **UI gred demo**.
>
> ✅ **STATUS 26 OKT: Diagnosis ini SEBAHAGIAN BESAR SELESAI.** Filament (T-71) menaikkan
> skor UI **2/10 → 7/10** dengan 66 resource CRUD, 6 widget dashboard dan halaman Tetapan.
> Baki jurang UI: borang masih **generik/dinamik**, bukan UI domain yang direka khusus.
> Cadangan di bawah dikekalkan sebagai rekod sejarah + rujukan keutamaan.

---

## Diagnosis: Apa Yang Sebenarnya Ada

> Nilai dalam kurungan = keadaan **26 Okt 2026** (dikemas kini).

| Lapisan | Ukuran | Kematangan |
|---|---|---|
| API backend | ~970 → **1,518 endpoint**, 220 → **962 ujian** | **Tinggi** |
| Isolasi tenant | 108 → **292 model**, 4 lapisan, diuji | **Tinggi** |
| Pematuhan Malaysia | EPF/SOCSO/EIS/PCB/HRDF + eksport | **Tinggi** |
| AI (ejen, boardroom, safety) | 11 ejen, 7 langkah, 16 ujian guard + **ADE (48 ujian)** | **Tinggi** |
| UI | 34 view × 5.5 KB → **66 resource Filament + 6 widget** | **Rendah → Sederhana (7/10)** |
| Integrasi bayaran | Tiada | **Sifar** |

**Contoh konkrit (asal):** `pos.blade.php` ialah 7.1 KB dengan 6 panggilan API. POS
sebenar memerlukan 800–1,500 baris. Yang ada ialah **rangka**, bukan produk.

> ✅ **26 Okt:** POS kini mempunyai **3 resource Filament** (`/admin/pos-jualan`,
> `/admin/pos-kaunter`, `/admin/pos-syif`) yang berfungsi untuk baca + konfigurasi.
> Juruwang sebenar masih memerlukan UI transaksi domain khusus.

---

## Cadangan 1 — Satu Modul, Dibuat Betul (KEUTAMAAN #1)

### Masalah

34 modul separuh siap tidak boleh dijual. Pelanggan pertama akan cuba POS,
jumpa ia tidak lengkap, dan berhenti mempercayai produk sepenuhnya.

> 📌 **Nota (26 Okt 2026):** "34" ialah angka **pada 24 Okt** apabila dokumen ini ditulis.
> Kod kini mempunyai **58 modul**. **Prinsip cadangan ini masih sah** — kedalaman
> mengalahkan keluasan. Yang berubah: UI kini wujud (66 resource Filament),
> jadi jurang beralih daripada "tiada UI" kepada "UI generik, bukan domain-specific".

### Cadangan

Siapkan **POS sahaja** sehingga boleh digunakan sepanjang hari bekerja:

| Ciri | Backend | UI | Status |
|---|---|---|---|
| Cipta draf jualan | ✅ `storeDraft` | ❌ | Perlu UI |
| Tambah/buang item | ✅ `addItem`/`removeItem` | ❌ | Perlu UI |
| Kemas kini kuantiti | ✅ `updateItem` | ❌ | Perlu UI |
| Selesaikan bayaran | ✅ `complete` | ❌ | Perlu UI |
| Refund | ✅ `refund` | ❌ | Perlu UI |
| Buka/tutup syif | ✅ `open`/`close` | ❌ | Perlu UI |
| Kiraan tunai | 🟡 | ❌ | Perlu tambah |
| Cetak resit | ❌ | ❌ | Perlu bina |
| Cari barang (SKU/barcode) | ✅ Inventory | ❌ | Perlu UI |
| Diskaun | ✅ `POSSaleDiscount` | ❌ | Perlu UI |
| **Mod offline** | ❌ | ❌ | **Perlu bina** |

### Kenapa POS

Ia modul pertama setiap PKS sentuh. Jika POS lancar, mereka percaya modul lain.
Jika POS lemah, tiada modul lain akan dilihat.

### Ciri pembeza: Mod Offline

Kedai di Malaysia kerap hadapi talian putus. PKS takkan terima POS yang mati
bila internet mati.

**Cadangan teknikal:**
- IndexedDB menyimpan katalog + harga
- Antrian jualan dalam `localStorage` bila offline
- Segerak automatik bila sambung
- Konflik dikendalikan dengan `client_uuid` (idempotency)

Ini membezakan AI BIJAN daripada StoreHub (yang juga offline) dan Zoho (cloud sahaja).

**Anggaran:** 2–3 minggu untuk satu UI POS sebenar.

---

## Cadangan 2 — WhatsApp sebagai Antara Muka Utama

### Masalah

Pemilik kedai tidak akan membuka dashboard 34 modul. Tetapi mereka **selalu**
di WhatsApp.

### Cadangan

Bukan "satu lagi inbox" (Omnichannel sudah ada). Sebaliknya, WhatsApp sebagai
**antara muka produk**:

```
Pemilik: berapa jualan hari ini

AI BIJAN: RM 4,280 (naik 12% berbanding semalam)
          • 3 invois lewat: RM 2,150
          • Stok 'Kotak 10x10' tinggal 8 unit
          • Purata tiket naik RM 3.20

Pemilik: buat PO untuk kotak

AI BIJAN: Draf PO #442
          Kotak 10x10 × 500 unit — RM 425
          Pembekal: Packaging Sdn Bhd (7 hari)
          Balas 'lulus' untuk hantar, atau 'ubah' untuk edit.

Pemilik: lulus

AI BIJAN: PO #442 dihantar. Pembekal dimaklumkan.
```

### Kenapa ini berkuasa

| Aspek | Kesan |
|---|---|
| Halangan penggunaan | Hampir sifar — tiada app untuk dimuat turun |
| Kelajuan maklum balas | Saat, bukan minit |
| Pembeza | Zoho/Odoo tidak boleh buat ini (tiada Boardroom, tiada kelulusan AI) |

**Anda sudah ada semua komponen:** Omnichannel (saluran), AI Agents, Boardroom
(kelulusan), Approval gates. Yang tiada hanyalah **sambungan** dan **pemacu WhatsApp**.

**Pagar wajib:** tindakan kewangan (PO, refund, bayaran) mesti melalui kelulusan
eksplisit dalam mesej, bukan andaian.

---

## Cadangan 3 — Laporan ROI Automatik

### Masalah

Dokumen Growth Engine kita sendiri mengaku: 1 kelebihan siap, 7 separa.
"AI urus perniagaan" tidak boleh dijual.

### Cadangan

Satu laporan yang mengira secara automatik daripada data sebenar:

```
LAPORAN ROI AI — Oktober 2026

Prospek dihubungi AI          47
  → Jadi pelanggan            12  (25.5%)
  → Nilai jualan        RM 18,400

Kerja manual dijimatkan:
  • Invois dijana automatik   82 dokumen
  • Dokumen dijana            34 dokumen
  • Susulan dihantar          47 mesej
  → Anggaran masa            38 jam

Kos AI bulan ini           RM 82
Pulangan                     224×
```

### Kenapa ini kritikal

Ini **satu-satunya nombor** yang membuat pelanggan memperbaharui langganan.
Bukan bilangan ejen. Bukan bilangan modul. Tetapi: *"RM82 → RM18,400"*.

### Data yang perlu

| Metrik | Sumber |
|---|---|
| Prospek dihubungi | `marketing_sequence_executions` |
| Jadi pelanggan | `orders` / `invoices` |
| Dokumen dijana | `documents` |
| Masa dijimatkan | `ai_tasks` (anggaran per jenis) |
| Kos AI | `ai_usage_events` |

Semua jadual ini **sudah wujud**. Ini cadangan dengan kos terendah dan impak tertinggi.

---

## Cadangan 4 — Mod Mudah Alih (PWA, 4 Skrin Sahaja)

### Masalah

Pemilik PKS Malaysia hidup di telefon. 34 view desktop tidak membantu.

### Cadangan

Satu PWA dengan 4 skrin:

| Skrin | Kandungan | Data sedia ada |
|---|---|---|
| **Hari ini** | Satu nombor besar: jualan hari ini + perubahan % | POS summary |
| **Amaran** | Stok rendah, invois lewat, anomali, peristiwa CCTV | BrainHub insights |
| **Tanya AI** | Kotak sembang + cadangan | Personal Assistant |
| **Kelulusan** | Senarai menunggu tanda tangan | Approval requests |

### Kenapa PWA, bukan app asli

| Aspek | PWA | iOS + Android asli |
|---|---|---|
| Kos bina | 1× | 2.5× |
| Kemas kini | Segera | Lewat kelulusan kedai app |
| Ciri telefon | 90% cukup | 100% |
| Notifikasi | Ya (2024+) | Ya |

Untuk PKS, PWA cukup. App asli hanya selepas 500+ pelanggan.

---

## Cadangan 5 — Onboarding 15 Minit

### Masalah

Zoho: 8/10 kemudahan. Odoo: 2/10 (perlu integrator). StoreHub: 9/10.

AI BIJAN: 4/10. Ini menghalang pendaftaran layan diri.

### Cadangan: 3 langkah, semuanya automatik

```
LANGKAH 1 — Apa perniagaan anda?
  [dropdown 19 industri / carian MSIC]
  → Blueprint dipilih automatik

LANGKAH 2 — Muat naik senarai anda
  [seret fail Excel/CSV]
  → AI petakan lajur sendiri (nama, harga, SKU)
  → Pratonton + betulkan
  → Import

LANGKAH 3 — Siapa pekerja anda?
  [jemput melalui e-mel atau senarai]
  → Peranan dicadangkan mengikut industri

SETIAP LANGKAH:
  boleh dilangkau · ada data contoh · ada pratonton
```

**Metrik kejayaan:** daftar → transaksi pertama < 30 minit.

**Apa yang perlu dibina:** pengimport CSV pintar (peta lajur automatik),
bukan wizard 20 halaman.

---

## Cadangan 6 — Harga Berasaskan Hasil

### Masalah

Pesaing caj per pengguna. PKS Malaysia purata 8–15 pekerja.

| Sistem | 15 pekerja | Kos/bulan |
|---|---|---|
| Zoho One | RM165 × 15 | RM2,475 |
| MS Dynamics | RM540 × 15 | RM8,100 |
| **AI BIJAN** | RM288 flat | **RM288** |

### Cadangan

Jadikan "**pengguna tanpa had**" tajuk utama, bukan nota kaki:

> *"RM288/bulan. Pengguna tanpa had. 80 juta token AI.
> Tiada caj per kerusi. Selamanya."*

**Kenapa ini menang:** keputusan pembelian PKS mudah difahami — satu nombor,
bukan kiraan per kerusi yang berubah setiap kali ambil pekerja.

---

## Cadangan 7 — Pematuhan sebagai Tajuk Jualan

### Masalah

Anda sudah ada pengiraan EPF/SOCSO/EIS/PCB/HRDF yang betul dan eksport CSV.
Tetapi ia terkubur dalam 34 modul.

### Cadangan

Jadikan ia **mesej pertama**, bukan ciri ke-20:

| Keperluan | Zoho | Odoo | StoreHub | AI BIJAN |
|---|---|---|---|---|
| EPF/SOCSO/EIS/PCB | ❌ | Separa | ❌ | ✅ |
| HRDF 1% tanpa had | ❌ | ❌ | ❌ | ✅ |
| Pagar consent PDPA | ❌ | ❌ | ❌ | ✅ |
| Audit trail penuh | Separa | Separa | ❌ | ✅ |

**Kenapa sukar ditiru:** pesaing global tidak akan melabur dalam butiran
Malaysia. Pesaing tempatan (StoreHub, Bukku) tiada AI.

Ini **persilangan unik**: pematuhan tempatan + AI + multi-tenant.

---

## Cadangan 8 — WhatsApp Customer Service AI

### Masalah

PKS terima berpuluh mesej sehari. Pemilik jawab sendiri pada 11 malam.

### Cadangan

AI menjawab pertanyaan rutin dengan **pengetahuan perniagaan sebenar**:

```
Pelanggan: ada stok saiz L?
AI: Ya, saiz L ada 3 unit (RM 89). Nak saya simpan?

Pelanggan: boleh COD?
AI: Boleh. Penghantaran RM 8, sampai esok (Kota Bharu).
    Nak teruskan pesanan?
```

**Guna:** modul `knowledge/*` yang baru dibina (vector search) + Omnichannel.

**Pagar wajib:** AI tidak boleh janji diskaun, tidak boleh sahkan bayaran,
tidak boleh mengubah harga. Mesti ada laluan eskalasi ke manusia.

---

## Cadangan 9 — Offline-First untuk semua operasi kritikal

### Masalah

Malaysia: talian putus kerap. PKS tidak boleh berhenti berniaga.

### Cadangan

| Modul | Perlu offline? | Sebab |
|---|---|---|
| POS | **Ya** | Tak boleh berhenti jual |
| Booking | Ya | Tempahan di lapangan |
| Inventory | Sebahagian | Semakan stok |
| Invoicing | Tidak | Boleh tunda |
| AI | Tidak | Perlu cloud |

**Prinsip:** "Jika anda perlu Terima Duit, ia mesti berfungsi offline."

---

## Cadangan 10 — Collective Intelligence (dengan pagar k-anonimiti)

### Masalah

Blueprint menyebut "belajar dari semua tenant" tetapi ini **belum dikodkan**
dan berisiko dari sudut PDPA.

### Cadangan: pelaksanaan berhati-hati

| Lapisan | Kandungan | Pagar |
|---|---|---|
| Tenant | Data penuh | Isolasi ketat |
| Agregat industri | Purata/median | **k ≥ 25 tenant** |
| Beneﬁt | Benchmark: "margin anda 22% vs purata industri 31%" | Opt-in sahaja |

**Pagar wajib:**
- Tiada data individu boleh dikembalikan
- k-anonimiti minimum 25
- Consent berasingan (bukan sebahagian terma perkhidmatan)
- Semakan undang-undang sebelum aktif

**Jangan bina ini sebelum ada 100+ tenant.** Tanpa skala, tiada agregat bermakna.

---

## Idea Tambahan (Nilai Tinggi, Kos Rendah)

### 11. Templat dokumen Malaysia siap pakai

Anda sudah ada 28 templat dokumen (`app/Documents/`). Tambah:
- Surat tawaran kerja
- Perjanjian sewa
- Notis berhenti kerja
- Invois dengan e-Invois LHDN

Ini ciri yang **segera berguna** dan membezakan daripada Zoho (yang ada templat
AS/UK, bukan Malaysia).

### 12. AI yang faham MSIC

Pelanggan taip: "Saya buat servis aircond."
AI: "Itu MSIC 43221 (Pemasangan Sistem Penghawa Dingin). Modul dicadang:
     Booking, Invoicing, Inventory, POS. Dafter ke?"

Ini mengurangkan onboarding daripada 20 halaman borang kepada satu ayat.

### 13. Amaran anomali (bukan janji ramalan)

Jangan janji "ramalan jualan AI". Janji yang boleh dibuktikan:

```
ANOMALI BULAN INI:
• Jualan Khamis 15hb turun 62% (purata Khamis: RM 3,800 → RM 1,440)
• Stok 'Cat Putih' keluar 3× lebih pantas dari biasa
• Invois #INV-2841 belum dibayar 47 hari (pelanggan selalu bayar < 14 hari)
```

Setiap satu **boleh disahkan** dengan data. Bukan ramalan, bukan andaian.

### 14. Mod "Apa Jika"

Ini idea yang tiada pesaing PKS buat:

```
Pemilik: apa jadi jika saya naikkan harga 10%?

AI BIJAN: Berdasarkan 1,847 transaksi:
          • Jika permintaan anjal -1.2: jualan turun 12%, untung naik 4%
          • Titik pulang modal: -8.5% jumlah unit
          • Risiko: 23% pelanggan membeli >3×/bulan (paling sensitif)

          Ujian dicadang: naik 5% pada 2 kategori dahulu, 30 hari.
```

Ini **menggunakan data POS sebenar** — pesaing global tiada akses.

### 15. Papan pemuka pemilik gred "satu pandangan"

Bukan 12 kad. Tetapi:

```
┌─────────────────────────────────────────────────┐
│  HARI INI                    RM 4,280  ▲ 12%   │
├─────────────────────────────────────────────────┤
│  ⚠ 3 perkara perlu perhatian anda               │
│     1. 3 invois lewat (RM 2,150) → hantar?      │
│     2. Stok kotak tinggal 8 unit → buat PO?     │
│     3. Juruwang Ali belum tutup syif            │
├─────────────────────────────────────────────────┤
│  Tanya apa sahaja →                             │
└─────────────────────────────────────────────────┘
```

Satu nombor, tiga tindakan, satu kotak. Bukan dashboard — **ringkasan keputusan**.

---

## Apa Yang TIDAK Dicadangkan

| Idea | Sebab tolak |
|---|---|
| Tambah 10 modul lagi | 34 rangka sudah lebih daripada cukup |
| App iOS/Android asli | PWA cukup, kos 5× lebih rendah |
| Sambung 6 integrasi serentak | Setiap satu perlu kelulusan berbulan-bulan |
| Face recognition sebagai ciri jualan utama | Liabiliti PDPA tinggi, nilai rendah |
| Automasi penuh tanpa manusia | Satu kesilapan hilang kepercayaan selamanya |
| Collective Intelligence sekarang | Perlu 100+ tenant sebelum bermakna |
| Voice interface sekarang | Belum dikodkan; tunggu permintaan berbayar |

---

## Urutan Pelaksanaan

```
Minggu 1–3   POS sebenar + mod offline
Minggu 4–5   PWA 4 skrin untuk pemilik
Minggu 6     Laporan ROI automatik
Minggu 7     Onboarding 15 minit (pengimport CSV pintar)
Minggu 8     10 pelanggan pertama: guna, ukur, betulkan
Minggu 9–10  Integrasi FPX (ToyyibPay/Billplz)
Minggu 11–12 e-Invois LHDN
Minggu 13–14 WhatsApp sebagai UI
Minggu 15–16 MySQL + backup + pemantauan produksi
Minggu 17+   White-label, reseller, Ads, Vision
```

**Prinsip:** *satu modul yang sempurna mengalahkan 34 modul yang separuh siap.*

---

## Metrik Kejayaan

| Metrik | Sasaran | Cara ukur |
|---|---|---|
| Masa daftar → transaksi pertama | < 30 minit | Timeline audit log |
| Pelanggan guna POS setiap hari | 10 (fasa 1) | Kiraan syif ditutup |
| Laporan ROI dilihat | > 70% pemilik/bulan | Log tontonan |
| Kadar pembaharuan | > 85% | `subscriptions` |
| NPS | > 40 | Tinjauan suku tahunan |

---

## Ringkasan: 5 Perkara Paling Penting

| # | Perkara | Sebab |
|---|---|---|
| 1 | **POS sebenar** (bukan rangka) | Modul pertama pelanggan sentuh |
| 2 | **WhatsApp sebagai UI** | Saluran utama PKS Malaysia |
| 3 | **Laporan ROI automatik** | Satu-satunya nombor yang mengekalkan pelanggan |
| 4 | **Integrasi FPX** | Tanpa bayaran sebenar, tiada transaksi |
| 5 | **Onboarding < 30 minit** | Halangan pendaftaran layan diri |

Semua lima ini boleh dibina dengan kod yang **sudah wujud**. Tiada satu pun
memerlukan ciri baharu dari blueprint.
