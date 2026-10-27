# AI BIJAN — Business Growth Operating System

**Status:** Dokumen strategik + semakan status kod
**Tarikh asal:** 24 Oktober 2026
**Dikemas kini:** 26 Oktober 2026
**Rujukan:** Peluasan BAB 81 — Competitive Advantage & Business Growth Engine

> **Prinsip dokumen ini:** Setiap kelebihan di bawah ditanda dengan status sebenar
> berdasarkan kod dalam `ai-bijan-saas`. Kita **tidak** mendakwa ciri yang belum
> dibina. Jangan jual janji "AI mengurus semuanya" — jual hasil yang boleh dibuktikan.
>
> ⚠️ **Jangan tukar status tanpa semak kod.** Semakan terakhir: 27 Okt 2026
> (59 modul · 962 ujian lulus · 66 resource Filament · 1,518 endpoint).

---

## 1. Posisi Produk

| | |
|---|---|
| **Tagline** | *Every Business Deserves a Boardroom* |
| **Perubahan narratif** | Daripada **mengurus** perniagaan → membantu perniagaan **berkembang** |
| **Apa AI BIJAN bukan** | Bukan set perisian berasingan. Bukan chatbot. Bukan ERP generik |
| **Apa AI BIJAN** | Satu sistem yang menghubungkan pemasaran, jualan, pelanggan, operasi, kewangan dan tenaga kerja AI — boleh diukur, dikawal, dikembangkan |

---

## 2. Sembilan Kelebihan

Legenda status kod: ✅ Ada & diuji · 🟡 Separa · ⛔ Tiada

### 81.1 B-Owner Command Centre

| | |
|---|---|
| **Status kod** | � **Banyak siap** — Filament `/admin` kini ada **CommandCentreWidget** (4 KPI: hasil, belum bayar, perbelanjaan, menunggu), 5 widget lain (trend 30 hari, pecahan saluran, pesanan terkini, invois tertunggak), halaman Tetapan Syarikat. Baki: satu endpoint agregat `GET /api/v1/owner/overview` |
| **BAB** | 24, 45 · **T-71** |

Satu dashboard untuk pemilik melihat keseluruhan perniagaan tanpa membuka setiap modul:
jualan, keuntungan, aliran tunai, prestasi cawangan/jabatan/staf, prospek, kadar penukaran,
pelanggan berulang, tunggakan bayaran, risiko dan tugasan yang memerlukan tindakan.
Termasuk soalan terus kepada AI: *"Kenapa keuntungan bulan ini turun?"*

**Perlu dibina:** satu endpoint agregat `GET /api/v1/owner/overview` yang menggabungkan
metrik dari POS, Invoicing, Finance, CRM, Marketing dan HRMS dalam satu panggilan.

**Kelebihan kompetitif:** pemilik melihat **apa**, **mengapa**, dan **apa patut dibuat** —
bukan tiga laporan berasingan.

---

### 81.2 B-Growth Engine

| | |
|---|---|
| **Status kod** | 🟡 B-Site (27 fail), B-Marketing (33 fail), Ads integration ada; penjejakan sumber→jualan belum bersatu |
| **BAB** | 53, 30 |

Sistem pertumbuhan yang menghubungkan laman web → pemasaran → jualan → pendapatan:
B-Site untuk laman & landing page, B-Marketing untuk kempen, integrasi Google/Meta Ads,
penjejakan sumber prospek hingga jualan, cadangan kempen/bajet berdasarkan prestasi,
analisis CAC dan pulangan pemasaran.

**Perlu dibina:** pautan atribusi `lead_source` → `order_id` supaya CAC boleh dikira
tanpa integrasi Ads sebenar.

**Kelebihan kompetitif:** AI BIJAN bukan sekadar hasilkan iklan — ia mengukur sama ada
iklan itu menghasilkan **jualan yang menguntungkan**.

---

### 81.3 B-Sales & Relationship Intelligence

| | |
|---|---|
| **Status kod** | 🟡 CRM (16 fail) + Marketing sequences ada; skor prospek dan cadangan tindakan belum |
| **BAB** | 54 |

CRM yang menekankan **tindakan**, bukan sekadar menyimpan rekod:
import & bersihkan senarai prospek, kesan prospek belum dihubungi, AI sediakan draf mesej
mengikut konteks, peringatan susulan, penilaian prospek, cadangan tindakan seterusnya,
rekod hasil (dihubungi/berminat/beli/tidak berjaya).

**Kelebihan kompetitif:** mengurangkan peluang jualan hilang kerana terlupa susulan.

---

### 81.4 B-Site AI Website Builder

| | |
|---|---|
| **Status kod** | 🟡 Struktur laman, halaman, tema, borang, domain ada; AI generate kandungan belum |
| **BAB** | 53 |

Pelanggan membina laman web berdasarkan jenis perniagaan: restoran, klinik, kontraktor,
kedai, syarikat acara. Halaman produk/servis/harga/tempahan, borang prospek terus ke CRM,
sambungan pembayaran & operasi, domain sendiri, penjenamaan, pelan white-label.

**Kelebihan kompetitif:** pelanggan tidak perlu urus laman web, CRM dan sistem tempahan
sebagai tiga produk berasingan.

---

### 81.5 B-Operations Industry Engine

| | |
|---|---|
| **Status kod** | 🟡 19 industry pack (direktori + blueprint); workflow per industri separa |
| **BAB** | 15, 43 |

Sistem operasi yang berubah mengikut sektor: restoran (POS, menu, inventori, cawangan),
klinik (janji temu, rekod, bil, akses berperanan), kontraktor (projek, pekerja lapangan,
bahan, kos, kemajuan), event (acara, vendor, bajet, jadual, bayaran), retail (stok,
pembelian, jualan, pelanggan).

**Kelebihan kompetitif:** satu platform dengan struktur operasi **khusus setiap industri**,
bukan satu sistem generik yang memaksa semua perniagaan bekerja sama.

---

### 81.6 B-AI Voice & Communication

| | |
|---|---|
| **Status kod** | ⛔ Tiada — Omnichannel ada inbox, tiada suara |
| **BAB** | 56A |

Lapisan komunikasi untuk panggilan, mesej dan janji temu: AI menjawab pertanyaan rutin,
membantu jadualkan janji temu, ringkasan perbualan, cadangan susulan, hubungkan perbualan
ke rekod CRM.

**Pagar wajib:** panggilan keluar, penghantaran mesej dan tindakan sensitif mesti tertakluk
kepada kebenaran, peraturan saluran dan kelulusan. Rujuk BAB 56C.6A untuk corak pagar.

---

### 81.7 B-Finance Intelligence

| | |
|---|---|
| **Status kod** | 🟡 Finance, Invoicing, Payroll, COGS ada; ramalan tunai dan amaran anomali belum |
| **BAB** | 14, 57 |

Menghubungkan aktiviti perniagaan dengan prestasi kewangan: aliran tunai & ramalan,
invois/perbelanjaan/hutang, margin mengikut produk/projek/cawangan, amaran transaksi
luar biasa, analisis kos operasi & cadangan penjimatan, integrasi pembayaran & e-Invois.

**Kelebihan kompetitif:** pemilik faham bukan sahaja **jumlah jualan**, tetapi juga
**keuntungan, kos dan risiko kewangan**.

---

### 81.8 B-Agent Workforce & Boardroom

| | |
|---|---|
| **Status kod** | 🟢 **Siap** — AI Agents (11 ejen), Boardroom (7 langkah), Personal AI (9 jenis) — semua diuji. Tambahan: **AgentStudio** (16 templat ejen, 37 ujian), **ADE** (4 fasa agentic, 48 ujian) |
| **BAB** | 16, 17, 20 · **BAB 85, 92** |

Sekumpulan AI khusus bekerja bersama: AI CMO (pemasaran), AI CFO (aliran tunai),
AI COO (operasi & inventori), AI Sales (prospek), AI Customer Service (tiket),
B-Boardroom (analisis merentas jabatan).

**Kelebihan kompetitif:** AI boleh menghubungkan isu **merentas fungsi** — contohnya
penurunan jualan, stok berlebihan dan kempen tidak menguntungkan dalam satu analisis.

**Pagar:** tindakan yang memberi kesan kepada wang, pelanggan atau sistem mesti melalui
kawalan akses dan kelulusan. Prompt guard (BAB 62) sudah dipasang.

---

### 81.9 B-Platform & White-Label Engine

| | |
|---|---|
| **Status kod** | 🟡 Multi-tenant, RBAC, AI Token Wallet, subscription plan ada; **reseller billing (komisen berulang) sudah dikodkan** dalam `Billing/` — white-label & payout automatik belum penuh |
| **BAB** | 4a, 4b, 6, 21, 22 |

Infrastruktur komersial: pelan langganan SaaS yang ditetapkan Super Admin, pakej token AI
berasingan daripada langganan, harga/margin/had per model, API key berasingan per tenant,
domain & logo & tema white-label, modul/seat/caj penggunaan tambahan, pengurusan reseller
dan pelanggan reseller.

**Kelebihan kompetitif:** AI BIJAN boleh beroperasi sebagai produk SaaS sendiri **dan**
menyokong rakan niaga yang mahu jual perkhidmatan berjenama sendiri.

---

## 3. Kelebihan Terbesar: Semuanya Saling Berhubung

Nilai utama bukan **bilangan modul**. Nilainya ialah **hubungan** antara modul.

```
B-Site & B-Marketing        →  Dapatkan pengunjung & prospek
        ↓
B-CRM & B-Sales             →  Urus prospek, susulan, penukaran
        ↓
B-Operations & B-Payment    →  Proses pesanan, hantar servis, rekod bayaran
        ↓
B-Finance & B-Analytics     →  Ukur hasil, margin, kos, aliran tunai
        ↓
B-Brain Hub & B-Boardroom   →  Analisis keseluruhan kitaran, cadang penambahbaikan
```

**Contoh operasi:** AI BIJAN mengesan jualan restoran menurun. Sistem menyemak data kempen,
kadar penukaran, menu, inventori dan margin. Ia mencadangkan kempen lebih sesuai — tetapi
**meminta kelulusan pemilik** sebelum mengubah bajet iklan atau menerbitkan promosi.

Itulah perbezaan antara AI yang **menjawab soalan** dengan AI yang **memahami aliran kerja
perniagaan**.

### Kelebihan unik yang pesaing tidak boleh tiru

**Kaitan CCTV dengan POS** (BAB 56C):

```
Kamera depan: 12 orang masuk hari ini
POS:          3 transaksi
→ Amaran: "9 pelanggan keluar tanpa beli — semak kawasan kaunter"
```

Hikvision dan Milestone **tidak boleh** buat ini kerana mereka tiada data POS.

---

## 4. Keperluan Teknikal Wajib

| Komponen | Keperluan | Status kod |
|---|---|---|
| **Multi-tenancy** | Data, memori, workflow, API key, log, konfigurasi terasing | ✅ **292 model** guna `BelongsToTenant` |
| **RBAC** | Peranan, cawangan, jabatan, had kelulusan | ✅ `config/aibijan.php` — **212 kebenaran** |
| **Integration Hub** | API, webhook, penyegerakan, pengurusan sambungan | 🟡 `McpStudio` (MCP) ada; **Connector Pack 20 sistem** belum (T-75) |
| **AI Governance** | Had token, kawalan kos, rekod penggunaan, kelulusan | ✅ Wallet + usage ledger + `PromptGuard` |
| **Event-driven** | `LeadCreated`, `OrderPaid`, `InvoiceOverdue` cetus workflow | ✅ Transactional outbox (`ServiceOS`, `FlowAutomation`) |
| **Audit & Security** | Audit log, rahsia, kawalan akses, pemulihan | ✅ + prompt guard + **0 advisori** (Laravel 12.70.1) |
| **Measurement** | Masa dijimatkan, kadar penukaran, kos AI, keuntungan | ✅ `/health` `/ready` `/metrics` + `ProofOfValue` (metrik hasil) |
| **Agentic Engineering** | Ejen bina/semak kod sendiri | 🟡 **ADE** — A1–A4 siap; pelaksanaan kod sebenar P2 |

---

## 5. Fasa Pelaksanaan

| Fasa | Skop | Status |
|---|---|---|
| **1 — Asas platform** | Tenant isolation, identiti, RBAC, billing, AI Wallet, audit, Integration Hub | ✅ ~90% |
| **2 — Pertumbuhan pelanggan** | B-CRM, B-Site, B-Sales, B-Marketing, susulan | 🟡 ~60% |
| **3 — Operasi industri** | POS, inventori, tempahan, kewangan, templat sektor | 🟡 ~70% |
| **4 — AI Workforce** | AI CMO/CFO/COO/Sales, Boardroom, voice, workflow multi-ejen | 🟡 ~65% (voice tiada) |
| **5 — Komersial** | White-label, reseller, API usage billing, laporan ROI | 🟡 ~40% |

---

## 6. Prinsip Jualan

> **Jangan jual janji "AI mengurus semuanya secara automatik".**
> Jual hasil yang boleh dibuktikan.

| Janji yang salah | Hasil yang boleh dibuktikan |
|---|---|
| "AI urus perniagaan anda" | "Tambah X% prospek ditukar jadi pelanggan" |
| "Automasi penuh" | "Kurangkan Y jam kerja manual sebulan" |
| "CCTV AI pintar" | "Kurangkan kehilangan stok Z% melalui amaran masa nyata" |
| "AI CFO" | "Ramalan aliran tunai 30 hari dengan ketepatan W%" |

Setiap dakwaan mesti disokong oleh **metrik yang diukur dalam sistem**, bukan anggaran.

---

## 7. Status Rujukan Luaran

| Rujukan | Status | Nota |
|---|---|---|
| **Chupjer** | Dirujuk | Gabungan POS + operasi + kesetiaan + pemasaran dalam satu platform |
| **CloseFlow** | Dirujuk | Tindakan susulan jualan, cadangan langkah seterusnya, semakan keselamatan mesej |
| **vervv.io** | ⚠️ **Belum disahkan** | Perlu semakan laman sebelum menetapkan ciri khusus |

---

## 8. Ringkasan Status Sebenar

| Kelebihan | Status | Ujian |
|---|---|---|
| 81.1 Owner Command Centre | 🟡 Separa | — |
| 81.2 Growth Engine | 🟡 Separa | — |
| 81.3 Sales Intelligence | 🟡 Separa | — |
| 81.4 B-Site AI Builder | 🟡 Separa | — |
| 81.5 Industry Engine | 🟡 Separa | — |
| 81.6 AI Voice | ⛔ Tiada | — |
| 81.7 Finance Intelligence | 🟡 Separa | ✅ Payroll export |
| 81.8 Agent Workforce | ✅ Ada | ✅ 60+ ujian AI |
| 81.9 Platform & White-Label | 🟡 Separa | ✅ Billing asas |

**Kesimpulan:** 1 kelebihan siap sepenuhnya, 7 separa, 1 belum bermula.
Asas platform (Fasa 1) hampir lengkap — nilai seterusnya ada pada mengukuhkan Fasa 2 dan 5.
