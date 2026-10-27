# Prospect → Blast → Close: Panduan Pelaksanaan (BAB 82)

**Tarikh:** 24 Oktober 2026
> **Blueprint:** `AI-BIJAN-MASTER-BLUEPRINT.md` — BAB 82
**Status kod:** Diimplementasikan & diuji (94 ujian baharu)

> ✅ **Disemak 26 Okt 2026:** angka dalam dokumen ini (94 ujian, 6 jadual Prospecting,
> 6 jadual Blast, 3 jadual ExecutiveAI, 5 jadual MediaStudio) **disahkan tepat**
> terhadap kod. Jadual `prospects`, `prospect_lists`, `prospect_sources`,
> `blast_campaigns`, `blast_messages` wujud.

---

## Ringkasan

Enjin jualan AI BIJAN terdiri daripada 4 modul yang saling menyambungkan:

```
┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│  PROSPECTING │ →  │    BLAST     │ →  │ SALES COPILOT│ →  │     CRM      │
│   (Fasa 1)   │    │   (Fasa 2)   │    │   (Fasa 3)   │    │  (sedia ada) │
├──────────────┤    ├──────────────┤    ├──────────────┤    ├──────────────┤
│ Cari & enrich│    │ Hantar &ukur │    │ Analisis &   │    │ Deal &       │
│ prospect     │    │ respons      │    │ draf         │    │ quotation    │
└──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘
        ↑                   ↑
        │                   │
   ┌────┴───────────────────┴────┐
   │  CONSENT GATE (WAJIB)       │
   │  + AUTONOMY LEVEL           │
   └─────────────────────────────┘
```

---

## 1. Modul Prospecting (`app/Modules/Prospecting/`)

### Yang Diaries

| Item | Details |
|---|---|
| Migration | `2026_10_24_000400_create_prospect_tables.php` (6 jadual) |
| Service | `ConsentService`, `DedupeService`, `CsvImporterService`, `PublicScraperService`, `RobotRules`, `ProspectScoringService` |
| Routes | 11 endpoint di `/api/v1/prospecting` |
| Ujian | 24 lulus |

### Jadual

| Jadual | Guna |
|---|---|
| `prospect_sources` | Sumber (CSV / API / scrape / manual) |
| `prospect_lists` | Kumpulan mengikut ICP |
| `prospects` | Prospek + consent + skor + status |
| `prospect_audit_logs` | Jejak audit |
| `scraping_jobs` | Job scraping (robots-aware) |
| `scraped_pages` | Halaman yang diambil (cache) |

### Consent Gate — Pagar Kritikal

Tiada mesej keluar tanpa **keempat-empat** syarat:

1. `consent_status = 'granted'`
2. `consent_evidence` tidak kosong
3. `consent_expires_at` belum lepas
4. Channelempangan ada (email/phone)

```php
// filepath: app/Modules/Prospecting/Services/ConsentService.php
$consent = app(ConsentService::class);

if (! $consent->bolehDihantar($prospect, 'email')) {
    // DITOLAK — jangan hantar
}
```

### Dedupe

| Jenis | Normalisasi |
|---|---|
| Gmail | Lowercase, buang titik, buang `+alias`, samakan `googlemail.com` |
| Domain lain | Lowercase, buang `+alias` sahaja |
| Telefon MY | E.164 (`+60123456789`) |

Dedup guna SHA-256 hash dengan unique index per tenant.

### Scraping — peraturan wajib

| Perkara | Nilai |
|---|---|
| `respect_robots` | `true` (default) — jangan ubah tanpa sebab |
| User-Agent | `AI-Bijan-Prospecting/1.0` — **jangan pretend to be browser** |
| Rate limit | 10 req/minit (configurable) |
| URL dihalang | `/login`, `/wp-admin`, `/cart`, `/download`, fail media |
| Tiada | CAPTCHA bypass, proxy rotation, data peribadi sensitif |

### Skor (0–100)

| Faktor | Berat |
|---|---|
| Consent granted | 25 |
| Ada e-mel | 20 |
| Ada syarikat | 15 |
| Ada telefon | 10 |
| Ada jawatapan | 10 |
| Disahkan | 10 |
| Ada website | 5 |
| Ada LinkedIn | 5 |

**Panas** ≥ 70 · **Suam** ≥ 45 · **Sejuk** < 45

---

## 2. Modul Blast Engine (`app/Modules/Blast/`)

### Yang Diaries

| Item | Details |
|---|---|
| Migration | `2026_10_24_000500_create_blast_tables.php` (6 jadual) |
| Service | `BlastService`, `OptOutService`, `RateLimiterService`, `DeliverabilityService`, `TemplateService` |
| Provider | `EmailProvider`, `SmsProvider`, `WhatsAppProvider` (adapter pattern) |
| Routes | 15 endpoint di `/api/v1/blast` |
| Ujian | 20 lulus |

### Empat Pagar Penghantaran

```
Pagar 1: Consent         → ConsentService::bolehDihantar()
Pagar 2: Penindihan      → OptOutService::disekat()
Pagar 3: Had kadar       → RateLimiterService::bolehHantar()
Pagar 4: Kelulusan       → BlastCampaign::bolehDihantar()
```

**Mana-mana pagar gagal → mesej TIDAK dihantar.** Mesej dilangkau
(`status = skipped`) dan sebabnya direkod.

### Had Kadar

| Had | Default | Env |
|---|---|---|
| Harian per tenant | 2,000 | `BLAST_DAILY_PER_TENANT` |
| Harian per domain | 500 | `BLAST_DAILY_PER_DOMAIN` |
| Sekejap e-mel | 20/s | `BLAST_EMAIL_PER_SECOND` |
| Sekejap SMS | 10/s | `BLAST_SMS_PER_SECOND` |
| Sekejap WhatsApp | 80/s | `BLAST_WHATSAPP_PER_SECOND` |

### Deliverability Auto-Pause

| Ambang | Nilai | Tindakan |
|---|---|---|
| Bounce rate | > 5% | Domain dijeda, kempen auto-pause |
| Complaint rate | > 0.1% | Domain dijeda, kempen auto-pause |

Skor reputasi 0–100: `-5` setiap 1% bounce, `-500` setiap 0.1% complaint.

### Opt-Out

- **RFC 8058** — header `List-Unsubscribe` + `List-Unsubscribe-Post: One-Click`
- **Suppression list** — kekal, unique per tenant + channel
- **Tarik consent** → `consent_status = denied` + suppression automatik
- `blast_unsubscribes` menyimpan token untuk landing page `/blast/unsubscribe/{tenant}`

### Template Tags

```
{{nama}} {{nama_penuh}} {{syarikat}} {{jawatapan}} {{kota}}
{{negara}} {{tawaran}} {{tarikh}} {{unsubscribe_link}}
```

Tag yang tidak dikenali **dibuang** (bukan dibiarkan kelihatan).

### Konfigurasi Provider

```env
# E-mel (mod 'log' = simulasi, jangan hantar ke alamat sebenar)
BLAST_EMAIL_MODE=smtp
BLAST_FROM_ADDRESS=no-reply@domain.my
BLAST_REPLY_TO=jualan@domain.my

# SMS (Twilio)
TWILIO_SID=...
TWILIO_TOKEN=...
TWILIO_FROM=+60123456789

# WhatsApp (Meta Cloud API)
WHATSAPP_ACCESS_TOKEN=...
WHATSAPP_PHONE_NUMBER_ID=...
```

---

## 3. Modul Sales Copilot (`app/Modules/SalesCopilot/`)

### Yang Diaries

| Item | Details |
|---|---|
| Service | `ReplyAnalyzerService`, `DealBriefService`, `FollowUpNudgerService` |
| Routes | 5 endpoint di `/api/v1/sales-copilot` |
| Ujian | 16 lulus |

### Klasifikasi Balasan

| Kategori | Trigger | Tindakan automatik |
|---|---|---|
| `interested` | minat, berapa harga, demo, boleh jumpa | Status → `replied` |
| `unsubscribe` | unsubscribe, remove me, jangan hantar | Consent → `denied` + suppress |
| `negative` | tidak minat, terlalu mahal | Status → `lost` |
| `not_now` | nanti, busy, next quarter | Follow-up dijadual |
| `wrong_person` | bukan orang betul | Tanda untuk cari kontak lain |
| `competitor` | sudah guna pesaing | Nota intel |
| `question` | soalan | Requires human |
| `unknown` | tiada isyarat | Requires human |

> **Penting:** Negasi didahulukan. `"Not interested"` diklasifikasi sebagai
> `negative`, **bukan** `interested` — walaupun perkataan "interested" terkandung.

### Deal Brief

Ringkasan 1 halaman: identiti · consent · skor + pecahan · isyarat · timeline
blast · ringkasan naratif · cadangan tindakan berikutnya.

### Follow-Up Nudger

**Hanya** prospect dengan `consent_status = granted` **dan**
`consent_evidence` tidak kosong. Yang tidak(br) di-churn.

---

## 4. Modul B-Executive AI (`app/Modules/ExecutiveAI/`)

### Yang Diaries

| Item | Details |
|---|---|
| Migration | `2026_10_24_000700_create_executive_ai_tables.php` (3 jadual) |
| Service | `AutonomyService`, `BriefingService` |
| Routes | 7 endpoint di `/api/v1/executive-ai` |
| Ujian | 17 lulus |

### Tahap Autonomi

| Aras | Nama | Boleh | Perlu Kelulusan |
|---|---|---|---|
| 0 | Tanya | Baca + jawab | ❌ |
| 1 | Cadang | Cadang tindakan | ❌ (sentiasa dicadangkan) |
| 2 | Draf | Jana draf | ✅ |
| 3 | Laksana | Eksekusi | ✅ satu klik |
| 4 | Automasi | Auto dalam had | ❌ + audit penuh |

### Hierarki Tindakan

| Tindakan | Aras Minimum |
|---|---|
| `read_data`, `ask_question` | 0 |
| `suggest_action`, `analyse` | 1 |
| `draft_email`, `draft_whatsapp`, `draft_video`, `draft_proposal` | 2 |
| `send_email`, `send_whatsapp`, `blast_campaign`, `create_task` | 3 |
| `automated_followup` | 4 |
| `send_payment`, `delete_data`, `change_autonomy` | 4 + **kelulusan eksplisit** |

> **Sentiasa perlu kelulusan manusia** (walaupun aras 4):
> `send_payment`, `delete_data`, `change_autonomy`, `change_tenant_settings`,
> `send_video_blast`

---

## 5. Modul Media Studio (`app/Modules/MediaStudio/`)

### Yang Diaries

| Item | Details |
|---|---|
| Migration | `2026_10_24_000600_create_media_studio_tables.php` (5 jadual) |
| Service | `VideoUnderstandingService`, `MediaModerationService`, `ConsentGateService`, `MediaRetentionService` |
| Routes | 12 endpoint di `/api/v1/media` |
| Ujian | 17 lulus |

### Pagar Persona

Persona video **wajib** `verification_status = 'verified'` + `verified_at`
terisi + ada `avatar_ref` sebelum boleh digunakan menjana video.

### Moderasi

Disekat: `violence`, `sexual`, `political`, `impersonation`.
Setiap video blast mesti ada watermark **"AI-generated"**.

### Retensi

Default 90 hari. Scheduler `media-retention-purge` (harian 03:30) memadam
fail fizikal + tandakan `deleted_at`.

---

## Scheduler Baharu

| Nama | Kekerapan | Fungsi |
|---|---|---|
| `blast-dispatch` | setiap minit | Hantar kempen berjalan/jadualkan |
| `prospecting-rescore` | setiap 10 minit | Skor semula prospect |
| `media-retention-purge` | harian 03:30 | Padam media luput |
| `executive-briefing-daily` | harian 07:30 | Jana briefing eksekutif |

Jalankan: `php artisan schedule:run` (cron setiap minit).

---

## Pesaing & Positioning

| Tahap | Pesaing | Kelemahan AI BIJAN |
|---|---|---|
| Cari prospek | Apollo, ZoomInfo, Clay | Connector berlesen + scraping robot-aware |
| Blast e-mel | Mailchimp, Brevo, Instantly | 4 pagar + deliverability auto-pause |
| Blast WhatsApp | ManyChat, WATI | Consent provenance penuh |
| Video blast | HeyGen, Synthesia | Persona perlu pengesahan identiti |
| Close deal | Regie.ai, 11x, Artisan | Brief + klasifikasi balasan BM/EN |

### 4 Pakej Produk (dicadangkan)

| Pakej | Isi | Kesan |
|---|---|---|
| **A — Safe Blast** | Enjin blast + deliverability + PDPA guard | Revenue paling cepat |
| **B — Hot Leads** | Skor + brief + follow-up + draf AI | Nilai per SME |
| **C — AI Draft** | Draf e-mel/WhatsApp/proposal/video | Masa jualan jimat |
| **D — Compliant Prospect Finding** | Connector + scraping + import + provenance | Baharu & berbeza |

---

## Rujukan Pantas

```php
// Consent check sebelum blast
use App\Modules\Prospecting\Services\ConsentService;
if (! app(ConsentService::class)->bolehDihantar($prospect, 'email')) { return; }

// Bina audiens
use App\Modules\Blast\Services\BlastService;
$hasil = app(BlastService::class)->binaPesanan($campaign, $prospects);

// Semak autonomy
use App\Modules\ExecutiveAI\Services\AutonomyService;
if (! app(AutonomyService::class)->permitted($level, 'send_email')) { /* kelulusan */ }

// Persona video
use App\Modules\MediaStudio\Services\ConsentGateService;
$semak = app(ConsentGateService::class)->semakPersona($persona);
```

---

## Perkara Belum Selesai

| Item | Keadaan |
|---|---|
| Provider e-mel sebenar | `BLAST_EMAIL_MODE=log` (simulasi) — perlu `smtp`/`mailgun` |
| WhatsApp template Meta | Perlu kelulusan template daripada Meta |
| Video generation | Provider (HeyGen/Synthesia) belum dikonfigurasi |
| AI multimodal | `AI_GATEWAY_LIVE=false` — fahami video guna transkripsi sahaja |
| `ffmpeg` | Tidak wajib — pipeline degrade graceful |
