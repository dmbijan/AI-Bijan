# Audit & Integrasi 4 Laman Web → AI BIJAN

Audit mendalam empat laman web, dan integrasi fungsi mereka ke AI BIJAN.

> Tarikh: 9 Nov 2026

---

## Ringkasan Audit

| Laman | Fokus | Modul AI BIJAN | Modul |
|---|---|---|---|
| [luxana.com.my](https://luxana.com.my/) | Pengurusan pesanan omnichannel | `App\Modules\OmniOrder` | Baharu |
| [post-bridge.com](https://www.post-bridge.com/) | Siaran silang sosial | `App\Modules\SocialBridge` | Baharu |
| [seowriting.ai](https://seowriting.ai/) | Kandungan & SEO | `App\Modules\ContentForge` | Baharu |
| [wasapbot.my](https://wasapbot.my/) | Automasi WhatsApp | `App\Modules\WhatsAppSuite` | Baharu |

---

## 1. Luxana → `OmniOrder`

**Audit:** Luxana ialah OMS untuk PKS Malaysia — satu baris gilir untuk pesanan
dari TikTok Shop, Shopee, WooCommerce, Shopify dan Fluent Forms. 80+ jenama,
RM4.2M/bulan dipproses. 3 plan (Essential/Flex/Lux).

### Fungsi diambil

| Fungsi Luxana | Implementasi AI BIJAN |
|---|---|
| Satu baris gilir semua saluran | `OmniOrder` + `OmniChannelConnection` (5+ saluran) |
| Kelulusan pukal (bulk approve) | `BposService` → `lulusPukal()` |
| Cetak label AWB | `cetakAwb()` |
| Push ke kurier (8+ kurier) | `OmniCourier` + `pushKeKurier()` |
| COD Tracker | `OmniCodPayout` |
| Duplicate Alert | `tasanPendua()` — telefon + jumlah,dalam 24 jam |
| Delivery Recovery | `pulihkanPenghantaran()` dengan cadangan tindakan |
| High-Risk Flag | `nilaikanRisiko()` — skor 0-100 |
| High-Value Track | `tandaNilaiTinggi()` — ambang RM500 |

### API (13 laluan)
`/api/v1/omni-order` — pesanan, kelulusan pukal, AWB, kurier, COD payout,
sambungan saluran, ringkasan.

**Kebenaran:** `omni_order.view` / `.manage` / `.execute`

---

## 2. Post Bridge → `SocialBridge`

**Audit:** Alat penyiaran silang untuk satu orang (founder). 10 platform,
1,405 pengguna, 3.47 juta post diterbitkan, 2 minit purata siapkan.

### Fungsi diambil

| Fungsi Post Bridge | Implementasi AI BIJAN |
|---|---|
| Siaran silang 10 platform | `SocialAccount` (10 platform) |
| Penjadualan | `SocialPost.scheduled_at` + `jadualkan()` |
| Pengurusan kandungan | `SocialPost` (draf/dijadual/diterbitkan) |
| Override per platform | `per_platform_overrides` + `untukPlatform()` |
| Carousel & media | `SocialPost.type` + `media` |
| Status berbeza setiap platform | `SocialPostTarget` |
| Video pukal | `SocialBridgeService::catatanTertunggak()` |
| Analitik | `ringkasan()` |

### API (9 laluan)
`/api/v1/social-bridge` — akaun, catatan, jadual, terbitkan, ringkasan.

**Kebenaran:** `social_bridge.view` / `.manage` / `.execute`

---

## 3. SEO Writing AI → `ContentForge`

**Audit:** penjana kandungan panjang untuk affiliate/SEO. 50,000+ pengguna,
50+ bahasa, model dari OpenAI/Anthropic/Google/DeepSeek, auto-post WordPress
(100 artikel pukal).

### Fungsi diambil

| Fungsi SEO Writing AI | Implementasi AI BIJAN |
|---|---|
| Artikel panjang | `ContentDocument` (6 jenis dokumen) |
| Senarai produk affiliate | `ContentDocument::TYPE_PRODUCT_LISTING` |
| Pemeriksa SEO (skor 0-100) | `skorSeo()` — 7 komponen, isu terperinci |
| Analisis SERP pesaing | `analisisSerp()` |
| Rangka (outline) | `janaRangka()` |
| 50+ bahasa | `config('content_forge.languages')` |
| Auto-post WordPress | `terbitkan()` (WP REST API) |
| 20+ model AI | `@ai_gateway` (model routing sedia ada) |

### API (11 laluan)
`/api/v1/content-forge` — dokumen, skor SEO, SERP, outline, terbit, laman.

**Kebenaran:** `content_forge.view` / `.manage` / `.execute`

---

## 4. WasapBot MY → `WhatsAppSuite`

**Audit:** Platform WhatsApp automation Malaysia — 33 juta mesej dihantar.
Chatbot ChatGPT, auto-responder, kata kunci, bulk messaging, validator nombor,
anti-ban delay, label management, warm-up, contact/group/label extractor,
order notification (WooCommerce/Shoppegram/Onpay).

### Fungsi diambil

| Fungsi WasapBot | Implementasi AI BIJAN |
|---|---|
| Chatbot AI | `WaAutoReply::TYPE_AI` |
| Auto-responder | `WaAutoReply::TYPE_WELCOME` |
| Set keyword | `WaAutoReply` (exact/contains/regex) |
| Bulk messaging | `WaCampaign` |
| Cloud-hosted | `WaDevice` + `WaMessage` |
| Number validator | `sahkanNombor()` — buang pendua & tidak sah |
| Anti-ban delay | `tetapanAntiBan()` — delay rawak + jeda berkala |
| Lead management / label | `WaContact.labels` |
| Extract contact/group/label | `WaContact` + `source` |
| Warm-up number | `mulaWarmup()` + `naikTahapWarmup()` — 4 tahap |
| Order notification | `WaOrderNotification` (7 pencetus) |
| Incoming webhook / API | Rute API standard + `WHATSAPP_SERVICE_URL` |

### API (16 laluan)
`/api/v1/whatsapp-suite` — peranti, kontak, sahkan nombor, autoreply, kempen,
mesej, pemberitahuan pesanan, warm-up, ringkasan.

**Kebenaran:** `whatsapp_suite.view` / `.manage` / `.execute`

---

## Keselamatan & Ketepatan

Semua modul mengamalkan prinsip yang sama seperti modul AI BIJAN sedia ada:

### 1. Tiada risiko — Tiada data invention
- Siaran sosial tanpa token OAuth → ditanda `failed` dengan sebab jelas
- WhatsApp tanpa `send_live=true` → mesej kekal `queued`, tidak berpura-pura dihantar
- SERP tanpa API → mengembalikan `sumber: estimasi-tempatan`, bukan data palsu

### 2. Anti-race condition
```php
// Kad hadiah, nombor giliran, bayaran
$kad = PosGiftCard::where('code', $kod)->lockForUpdate()->first();
```

### 3. Nombor telefon Malaysia dinormalkan
```php
'01X-XXXXXXX' → '60X-XXXXXXX'
'0123456789'  → '60123456789'
```

### 4. Wang dalam integer SEN
Tiada float — elak ralat pembundaran.

### 5. Penenggalan tenant
Setiap model guna `BelongsToTenant` (skop global + anti-pemalsuan `tenant_id`).

---

## Jadual Baharu

| Jadual | Modul |
|---|---|
| `omni_orders`, `omni_couriers`, `omni_channel_connections`, `omni_cod_payouts` | OmniOrder |
| `social_accounts`, `social_posts`, `social_post_targets` | SocialBridge |
| `content_documents`, `content_sites` | ContentForge |
| `wa_devices`, `wa_contacts`, `wa_campaigns`, `wa_auto_replies`, `wa_order_notifications`, `wa_messages` | WhatsAppSuite |

---

## Pengesahan

| Semakan | Keputusan |
|---|---|
| Ujian pengasingan 4 modul | **20/20 lulus**, 105 assertions |
| Laluan API | **49** (13 + 9 + 11 + 16) |
| Kebenaran baharu | 12 (4 modul × 3 tindakan) |
| Pint | **passed** |
| Sintaks | **0 ralat** pada 46 fail |
| Migrasi | 4 migrasi, 17 jadual |

---

## Nota

- Semua modul **tidak bersentuhan** dengan modul sedia ada — integrasi tambahan.
- Modul `orders`, `marketing`, `content_studio` sedia ada tidak diubah.
- Panel admin Filament untuk modul ini boleh ditambah sebagai langkah susulan.
