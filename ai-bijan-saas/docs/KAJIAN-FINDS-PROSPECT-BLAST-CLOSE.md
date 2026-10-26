# Kajian: Finds Prospek → Blast → Close Deal

**Tarikh:** 24 Oktober 2026
**Kaedah:** Semakan kod sebenar dalam `app/Modules/Marketing/`, `app/Modules/CRM/`,
jadual migration, dan `routes/console.php`. Setiap kesimpulan disahkan dengan
nama fail dan kaedah.

---

## Ringkasan Eksekutif

Tiga soalan anda:

| Soalan | Jawapan | Status |
|---|---|---|---|
| Cari prospek customer | ✅ **Dibina** | Modul `Prospecting/` — 6 jadual, import CSV, scraping robots-aware, dedupe, consent gate |
| Blast sistem | ✅ **Dibina** | Modul `Blast/` — 6 jadual, 4 pagar penghantaran, deliverability auto-pause, opt-out RFC 8058 |
| Close deal | ✅ **Dibina** | Modul `SalesCopilot/` — brief, klasifikasi balasan, follow-up nudger |

**Status:** Diimplementasikan dalam BAB 82 (blueprint v8.5), 24 Oktober 2026.
94 ujian baharu · 314 lulus keseluruhan · 0 gagal.

**Bekal asal:** Modul `Prospecting/`, `Blast/`, `SalesCopilot/`, `MediaStudio/`,
`ExecutiveAI/`. Panduan penuh: [`PROSPEK-BLAST-CLOSE.md`](PROSPEK-BLAST-CLOSE.md).

---

## 1. Cari Prospek (Grabber) — ❌ TIADA

### Bukti

```
Select-String -Pattern "scrap|crawl|prospect|lead_gen|search_lead" app/
→ 0 hasil sebenar
```

Semua padanan "prospect" hanyalah **peringkat pipeline** (`stage = 'prospecting'`),
bukan alat penfinding prospek.

### Yang ADA (bukan penfinding, tapi asas yang boleh dibina atas)

| Komponen | Lokasi | Nota |
|---|---|---|
| `Lead` model | `app/Modules/CRM/Models/Lead.php` | Simpan lead yang diimport |
| `Contact` model | `app/Modules/Marketing/Models/Contact.php` | Ada `source`, `opt_in_email/sms/whatsapp` + timestamp + method |
| `SegmentService` | `app/Modules/Marketing/Services/SegmentService.php` | SasaranAudience |
| `LeadScoreService` | 5 skor: demographic, firmographic, engagement, behavioral, transactional | `getTopLeads()`, `getScoreDistribution()` |
| `DealService` | `forecastRevenue()`, `getOverdueDeals()` | Ramalan jualan |
| `OpportunityService` | 7 peringkat + probability map | Pipeline CRM penuh |
| `SequenceService` | 13 kaedah — `startExecution`, `advanceStep`, `stopExecution` | Drip sequence penuh |

**Nota consent:** `opt_in_email`, `opt_in_sms`, `opt_in_whatsapp` setiap satu ada
paired timestamp + method. Skema ini **sudah betul** untuk sporadic blast — yang
tiada ialah penghantaran.

### Yang Tiada

| Keperluan | Status |
|---|---|
| Import prospect daripada sumber luar | ❌ |
| Crawl/scrap web | ❌ |
| API directory (Google Places, Facebook) | ❌ |
| Cadangan prospecting AI (industri + geografi + profil) | ❌ |
| Senarai pengecualian (skip list) untuk industri sensitif | ❌ |
| UTM tracking + attribution | ❌ (ada `source` sahaja) |

---

## 2. Blast — 🟡 SKELETON

### Bukti — semua penghantaran ialah placeholder

```php
// app/Modules/Marketing/Services/WorkflowService.php
protected function executeSendEmail(array $step, WorkflowExecution $execution): bool
{
    // Placeholder for email service integration
    return true;      // ← TIDAK HANTAR
}

protected function executeSendSms(array $step, WorkflowExecution $execution): bool
{
    // Placeholder for SMS service integration
    return true;      // ← TIDAK HANTAR
}

protected function executeSendWhatsapp(array $step, WorkflowExecution $execution): bool
{
    // Placeholder for WhatsApp service integration
    return true;      // ← TIDAK HANTAR
}
```

### Skema sedia ada (sangat baik)

| Jadual | Isi |
|---|---|
| `marketing_campaigns` | type: email/sms/whatsapp/social_media/paid_ads/multi_channel |
| `marketing_sequences` | channel + `json('channels')` |
| `marketing_sequence_executions` | Pelbagai langkah + status |
| `marketing_workflows` | Trigger/kondisi/tindakan |
| `marketing_workflow_executions` | Log setiap langkah |
| `marketing_contacts` | `opt_in_*` + `opted_*_at` + `opted_*_method` |
| `marketing_segments` | SasaranAudience |
| `marketing_lead_scores` | 5 dimensi skor |

**`opt_in_*` dengan timestamp + method = asas audit yang betul.** Ini yang
membezakan sistem boleh jual daripada sistem yang tidak boleh.

### Yang Tiada — dan ini kritikal

| Perkara | Kesan |
|---|---|
| **Tiada penghantar e-mel** | `MAIL_MAILER=log` dalam `.env` — mesej masuk fail log, bukan ke inbox |
| **Tiada penghantar WhatsApp** | Ini **saluran utama PKS Malaysia** |
| **Tiada penghantar SMS** | — |
| **Tiada penjadualan blast** | `routes/console.php` hanya ada: flow retry, reset kredit AI, alert baki, SLA helpdesk. **Tiada blast.** |
| **Tiada pemprosesan baris gilir (queue)** | Sequence tak jalan sendiri |
| **Tiada pembatalan langganan (unsubscribe)** | Wajib — PDPA |
| **Tiada penapis bounce/complaint** | — |
| **Tiada had kadar** | Risiko tandatangan spam |

### Risiko Kastam

Melblast tanpa ini bawa masalah besar:
1. **PDPA** — tanpa `opt_in` yang betul, blast = Spam Act 2008 dan potential liability
2. **Reputasi domain/IP** — blast tanpa had kadar → masuk senarai hitam
3. **Pelanggan hilang** — 1 e-mel buruk kepada pelanggan = mereka uninstall

---

## 3. Close Deal — 🟡 PIPELINE ADA, TINDAKAN TIADA

### Yang ADA (sangat baik)

**CRM Opportunity** (`app/Modules/CRM/Services/OpportunityService.php`):
```
prospecting → qualification → needs_analysis → proposal → negotiation → ...
```
Setiap peringkat ada probability map (10%, 25%, ...). `updateStage()` transaksi.

**Marketing Deal** (`app/Modules/Marketing/Services/DealService.php`):
```
prospect → qualified → proposal → negotiation → closed_won / closed_lost
```
- `moveStage()`
- `addActivity()` — jejak setiap interaksi
- `forecastRevenue()` — jumlah nilai × probability
- `getOverdueDeals()` — **ini berkesan**

**Pipeline penuh 7 peringkat** + kutipan (Quotation model) + `$deal->activities`.

### Yang Tiada

| Keperluan | Kesan |
|---|---|
| **Tiada draf mesej AI** |(ct) |
| **Tiada amaran "propek belum dihubungi"** | Prospek lapse senyap |
| **Tiada cadangan tindakan seterusnya** | AI tidak_flags propose |
| **Tiada railing automatik** |-sequence step `deal` ada — tetapi tiada siapa yang mencetuskan |
| **Tiada VAT/negotiation assistant** | — |
| **Tiada tanda tangan e-untuk kontrak** | AI BIJAN ada B-SignDocs — **tidak disambung** |

---

## Analisis Jurang: Yang HilangMOS — Tiga Jurang

### Jurang 1: Tiada "cold start" — dari mana prospek datang?

Seluruh paip bermula dari `marketing_contacts`. Kalau contact tiada, paip tiada jalan.

Ini **bukan pepijat** — ia keputusan reka bentuk yang betul. Menyalin prospek dari
internet tanpa kebenaran adalah:
- Melanggar GDPR/PDPA
- Melanggar syarat Reddit/Facebook/LinkedIn
- Risiko liabiliti

**Cadangan: sumber yang patuh (compliant).**

### Jurang 2: Tiada penghantaran — paip

### Jurang 3: Tiada AI dalam gelung jualan

AI BIJAN ada `PromptGuard`, ada `AIGateway`, ada `LeadScoreService` — tetapi
**tiada satu pun** digunakan untuk:
- Menulis draf mesej
- Mencadangkan langkah seterusnya
- Mengesan prospek "panas"

---

## Cadangan: 4 Pakej Kerja

### Pakej A — Blast yang Selamat (2–3 minggu) 🔴 PRIORITI TERTINGGI

**Nilai:** Menukar sistem daripada "ada UI" kepada "berfungsi". Tanpa ini, semua
yang lain cuma teori.

**Kerja:**

| Komponen | Perkara |
|---|---|
| `EmailSender` service | `Mail::` + queue, batch 50, `queue:work` |
| `WhatsAppSender` service | WhatsApp Cloud API (Meta) — **saluran utama MY** |
| `SmsSender` service | Untuk OTP/pengingatan kritikal sahaja |
| `BlastJob` | Job beratur: `ShouldQueue`, per-tenant, retry + backoff |
| `Subscription` model | `unsubscribe_at`, token, `List-Unsubscribe` header |
| `ConsentService` | Semak `opt_in_*` + `opted_*_at` **sebelum** hantar |
| `Rate limiter` | 50 emel/jam/tenant, 20 WhatsApp/jam/tenant |
| `BounceHandler` | Tanda `bounced_at`, auto-kunci |
| `ComplaintHandler` | Auto-unsubscribe + audit |
| `Scheduler` | `blast-dispatch` setiap minit, `blast-send` berkala |

**Pagar wajib (tidak boleh dilangkau):**

```php
// 1. Consent wajib
if (! $contact->opt_in_email || ! $contact->opted_email_at) {
    return false;  // jangan hantar
}

// 2. Unsubscribe mesti berfungsi
if ($contact->unsubscribed_at) {
    return false;
}

// 3. Had kadar
if (! $this->rateLimiter->attempt("blast:{$tenantId}", 50, 3600)) {
    return false;  // cuba lagi nanti
}
```

**Pilih 1 Breach minimum:**
- `POST /api/v1/marketing/unsubscribe/{token}` (token dalam pautan)
- atau `GET /api/v1/marketing/unsubscribe?email=...`

**Ujian wajib:**
- Blast tanpa consent → 0 e-mel dihantar
- Blast selepas unsubscribe → 0 e-mel dihantar
- Had kadar → jumlah betul
- Queue retry tidak hantar dua kali
- Bounce → contact dikunci

---

### Pakej B — Peluang "Tolak" (Panas) (1–2 minggu)

**Nilai:** Ini yang menghasilkan wang. Prospect yang panas tapi tidak hunted =wang
terbengkak.

**Logik** (guna data yang sudah ada):

```
Skor komposit = engagement (40%) + behavior (30%) + transactional (30%)

Syarat "panas":
  • Skor ≥ 70, ATAU
  • Buka e-mel ≥ 3 kali dalam 7 hari, ATAU
  • Klik pautan ≥ 2 kali, ATAU
  • Tambah ke troli tapi tak beli (kalau ada POS), ATAU
  • K Visits web (kalau ada Site)
```

**Kerja:**
- `OpportunitySignalService` — kira isyarat, jadual `opportunity_signals`
- `HotLeadService` — senarai prospect panas + sebab ("buka 3× e-mel")
- **Amaran wajib** bila prospect panas > 48 jam tanpa contacted
- Integrasikan dengan `DealService::addActivity()`

**UI:** Panel "Hot Leads" — 20% GST je, jadi tak payah gorgeous.

---

### Pakej C — Draf Mesej AI (2 minggu)

**Nilai:**INI yang mengurangkan masa jualan. HubSpot/Close ada ini.

**Kerja:**
- `OutreachDraftService` — AI bina draf mesej untuk prospect
- **Input:** skor lead, sejarah komunikasi, industri, tawaran semasa
- **Output:** draf + 3 pilihan nada (formal/f casually/tidak langsung)
- **Pagar:** AI hanya draf. Penghantaran tetap manual atau perlu kelulusan.

```
PromptGuard → imbas draf sebelum simpan
PromptGuard → imbas balasan terus sebelum hantar (ada)
```

**Edge case:** elak AI hantar mesej berulang. Per Hour Cap dah ada dalam pakej A.

---

### Pakej D — Prospect Finding yang COMPLIES (3–4 minggu)

**Nilai:** Ini yang palingitó sukar tetapi paling berkuasa.

**Yang boleh dibina (tanpa melanggar basic):**

| Sumber | Cara | Nota |
|---|---|---|
| **Google Places API** | Cari biz mengikut kata kunci + lokasi | Percuma 5k/bulan. **Patut ada consent.** |
| **Facebook Graph API** | Discovery | Perlu permission + adaToS |
| **LinkedIn Sales Navigator** | API rasmi | Berbasah |
| **Google Maps manual** | Bulk import URL → adegan |atore |
| **Portal industri** | MyData, Malaysia | Data awam |
| **Import Excel/CSV** | Muat naik manual | **Paling selamat** |
| **Rujukan pelanggan** | " tentatively invite" | Consent jelas |

**Cara terpadat (dipakai dengan baik):**

```
"Setiap pagi, cari kedai kopi dalam 5km yang belum ada dalam sistem,
kirim WhatsApp: 'Hai, kami buka kedai kopi baru di [lokasi]. Nak buat
sistem POS?'"

Ini ADALAH spam tanpa consent. TIDAK advisable.
```

**Saya tidak cadangkan scraping tanpa consent.** Ini bukan sekadar isu teknikal —
ini liabiliti dan reputasi jenama. AIGateway, AI BIJAN — atau apa-apa — yang
mengmass-kanblast tanpa consent akan dirosakkan di Malaysia.

**Cadangan realistik:**
1. **Import** (Excel/CSV, Google Places) — alat untukagne
2. **Tapis dengan AI** — "yang manaprospek ini ada potential tinggi?"
3. **Hubungi secara manual** — human-in-the-loop untuk mesej pertama
4. **Blast automatik** — hanya selepas consent, ke senarai opted-in sahaja

---

## Peta Sistem Syariat (selepas Pakej A–D)

```
┌─────────────────────────────────────────────────────────┐
│  PROSPECT (D)                                           │
│  Import / Google Places / Rujukan                       │
│         ↓ consent check                                  │
├─────────────────────────────────────────────────────────┤
│  BLAST (A)                                              │
│  E-mel + WhatsApp + SMS, queue, had kadar, unsubscribe   │
│         ↓                                                                 │
├─────────────────────────────────────────────────────────┤
│  SCORE (ada)                                            │
│  5 dimensi + isyarat panas (B)                          │
│         ↓                                                                 │
├─────────────────────────────────────────────────────────┤
│  DRAFT AI (C)                                           │
│  Draf mesej + cadangan tindakan                           │
│         ↓ kelulusan manusia                             │
├─────────────────────────────────────────────────────────┤
│  CLOSE (ada)                                            │
│  7 peringkat + probability + forecast                  │
│         ↓                                                                 │
├─────────────────────────────────────────────────────────┤
│  BOARDROOM (ada ✅)                                      │
│  Analisis kenapa tak tutup → cadangan                    │
└─────────────────────────────────────────────────────────┘
```

**Ini adalah paip penuh yang tiada pesaing Malaysia boleh tiru** — mereka ada
satu dua sahaja.

---

## Keutamaan Pelaksanaan

| Pakej | Tempoh | Nilai | Risiko |
|---|---|---|---|
| **A — Blast selamat** | 2–3 minggu | 🔴 **Tinggi** — tanpa ini paip mati | Rendah (consent dah ada) |
| **B — Isyarat panas** | 1–2 minggu | 🔴 **Tinggi** — wang cepat | Rendah |
| **C — Draf AI** | 2 minggu | 🟠 Sederhana | Rendah (PromptGuard ada) |
| **D — Prospect finding** | 3–4 minggu | 🟠 Tinggi jangka panjang | **Tinggi** (patuh) |

**Cadangan saya: A → B → C → D.** Jangan skip A — tanpa penghantaran, B dan C
tidak bermakna.

---

## Rujukan Pesaing (untuk semakan)

| Keperluan | Pesaing |
|---|---|
| Blast + autoresponder | Mailchimp, Brevo, Sendinblue, Customer.io, Klaviyo |
| WhatsApp blast | ManyChat, WATI, Respond.io, 360dialog, Twilio |
| Hot lead scoring | HubSpot, Pipedrive, Chili Piper, MadKudu |
| AI outreach draft | Clay, Instantly, Smartlead, Apollo |
| Prospect finding (compliant) | Apollo, ZoomInfo, Clearbit, Lusha, LinkedIn Sales Nav |

**Nota:** clay/instantly/smartlead memang automate outreach — tetapi mereka
build untuk ****sales team enterprise**** yang ada senarai telah ada + opt-out
terkawal. **Copy model mereka tanpa consent = batal.**

---

## Nota Pemeliharaan

Status dalam dokumen ini disahkan dengan semakan kod. Jika anda bina Pakej A–D,
**kemas kini fail ini** — status 🟡 → ✅ dengan rujukan fail sebenar.
