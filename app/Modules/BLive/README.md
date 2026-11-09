# B-Live AI - AI Human Live Host Platform

> Your Digital Human. Your AI Brain. Your Live Business.

Modul ini menyediakan hos digital AI untuk=live langsung: avatar fotorealistik,
suara semula jadi, otak perbualan, kawalan hybrid manusia + AI, dan penstriman
ke beberapa platform.

---

## 1. Gambaran Keseluruhan

B-Live AI ialah modul Laravel (modular monolith) yang menyepadukan enjin AI dan
media sedia ada dalam ekosistem AI BIJAN:

| Keperluan | Implementasi | Nota |
| --- | --- | --- |
| Host persona | `blive_host_personalities` | 7 archetype, bahasa, tone, arahan sistem |
| Avatar | `blive_avatars` + `blive_render_jobs` | Enjin `simulated` / `metahuman` / `http` |
| Suara | Modul `VoiceStudio` sedia ada | ElevenLabs - tiada TTS digandakan |
| Otak AI | `AIGateway` + `HostBrainService` | 6 fasa gelung keputusan |
| Penstriman | `blive_stream_destinations` | YouTube / Facebook / TikTok |
| Analitik | `blive_live_metrics` | Metrik baris, skema stabil |

**Per Woodland**: B-Live tidak membina enjin avatar atau TTS sendiri. Ia
membina lapisan penciptaan, kawalan, identiti, pematuhan dan monetisasi.

---

## 2. Struktur Direktori

```
app/Modules/BLive/
├── BLiveServiceProvider.php
├── config/blive.php
├── database/migrations/
│   └── 2026_11_09_001000_create_blive_tables.php
├── Http/Controllers/
│   ├── AvatarController.php
│   ├── HostPersonalityController.php
│   └── LiveSessionController.php
├── Models/
│   ├── Avatar.php
│   ├── ConsentRecord.php
│   ├── HostPersonality.php
│   ├── LiveControl.php
│   ├── LiveMessage.php
│   ├── LiveMetric.php
│   ├── LiveSession.php
│   ├── ModerationEvent.php
│   ├── RenderJob.php
│   └── StreamDestination.php
├── routes/api.php
└── Services/
    ├── AvatarEngineService.php    # abstraksi enjin render
    ├── AvatarService.php          # B-Avatar Creator
    ├── ConsentService.php         # pagar identiti & kebenaran
    ├── HostBrainService.php        # B-AI Brain + gelung keputusan
    ├── LiveControlService.php     # kawalan hybrid manusia + AI
    ├── LiveSessionService.php     # kitaran hidup sesi live
    └── StreamingGatewayService.php # penstriman multi-platform
```

---

## 3. Permintaan API

Base URL: `/api/v1/blive`

### Host personality
| Method | Endpoint | Kebenaran |
| --- | --- | --- |
| GET | `/personalities` | `blive.view` |
| POST | `/personalities` | `blive.manage` |
| GET | `/personalities/{id}` | `blive.view` |
| PUT | `/personalities/{id}` | `blive.manage` |
| DELETE | `/personalities/{id}` | `blive.manage` |

### Avatar
| Method | Endpoint | Kebenaran |
| --- | --- | --- |
| GET | `/avatars` | `blive.view` |
| POST | `/avatars` | `blive.manage` |
| GET | `/avatars/{id}` | `blive.view` |
| PUT | `/avatars/{id}` | `blive.manage` |
| DELETE | `/avatars/{id}` | `blive.manage` |
| POST | `/avatars/{id}/generate` | `blive.execute` |

### Kebenaran (consent)
| Method | Endpoint | Kebenaran |
| --- | --- | --- |
| GET | `/consents` | `blive.view` |
| POST | `/consents/{id}/grant` | `blive.manage` |
| POST | `/consents/{id}/revoke` | `blive.manage` |

### Sesi live
| Method | Endpoint | Kebenaran |
| --- | --- | --- |
| GET | `/live-sessions` | `blive.view` |
| POST | `/live-sessions` | `blive.manage` |
| GET | `/live-sessions/{id}` | `blive.view` |
| POST | `/live-sessions/{id}/start` | `blive.execute` |
| POST | `/live-sessions/{id}/stop` | `blive.execute` |
| POST | `/live-sessions/{id}/takeover` | `blive.execute` |
| POST | `/live-sessions/{id}/release` | `blive.execute` |
| POST | `/live-sessions/{id}/pause-ai` | `blive.execute` |
| POST | `/live-sessions/{id}/resume-ai` | `blive.execute` |
| POST | `/live-sessions/{id}/speak` | `blive.execute` |
| POST | `/live-sessions/{id}/emergency-stop` | `blive.execute` |
| POST | `/live-sessions/{id}/comments` | `blive.execute` |
| GET | `/live-sessions/{id}/messages` | `blive.view` |
| GET | `/live-sessions/{id}/metrics` | `blive.view` |

### Penstriman
| Method | Endpoint | Kebenaran |
| --- | --- | --- |
| GET | `/live-sessions/{id}/channels` | `blive.view` |
| POST | `/live-sessions/{id}/channels` | `blive.manage` |
| POST | `/live-sessions/{id}/channels/{destinationId}/start` | `blive.execute` |

---

## 4. Kawalan Hybrid Manusia + AI

| Mod sesi | Tingkah laku |
| --- | --- |
| `ai_assisted` | AI bercakap; manusia boleh ambil alih pada bila-bila masa |
| `ai_autonomous` | AI bercakap sepenuhnya, tetapi henti kecemasan sentiasa ada |
| `human_led` | Manusia bercakap; AI hanya mencadangkan |

Peraturan operasi yang dipaksakan kod:

1. Manusia sentiasa mempunyai kuasa mengambil alih.
2. AI tidak boleh mengabaikan arahan berhenti.
3. Transaksi, diskaun dan janji jualan tertakluk kepada polisi diluluskan.
4. AI tidak boleh mencipta harga atau fakta produk.
5. Identiti manusia digital mesti didedahkan sebagai AI presenter.

Setiap arahan direkod dalam `blive_live_controls` (jejak audit) bersama
`issued_by`, `user_id`, `payload`, `result` dan `latency_ms`.

---

## 5. AI Brain - Gelung Keputusan

```
Receive Live Event
       v
Classify Intent          # greeting, price_inquiry, order_intent, ...
       v
Retrieve Knowledge       # produk diluluskan + knowledge base syarikat
       v
Check Business Rules     # harga / diskaun mesti diluluskan
       v
Check Human Instructions # arahanakawaal semasa
       v
Generate Response        # AIGateway -> GatewayService::call()
       v
Safety & Fact Validation # tiada harga karangan, eskalasi bila tidak pasti
       v
Voice + Animation        # VoiceStudio + enjin avatar
       v
Broadcast
       v
Record Analytics
```

### Keselamatan

| Semakan | Tindakan |
| --- | --- |
| Harga tanpa kelulusan | Ditolak, eskalasi kepada manusia |
| Jawapan "tidak pasti" | Ditolak, eskalasi kepada manusia |
| Pengisytiharan AIrequired | Avatar tanpa `is_ai_disclosed` tidak layak live |
| Prompt injection | Diimbas oleh `PromptGuard` AIGateway sebelum model dipanggil |

---

## 6. Enjin Avatar

Kontrak HTTP stabil dengan renderer luaran:

```
POST {base_url}/avatars        -> { "job_id": "...", "status": "queued" }
GET  {base_url}/avatars/{job}  -> { "status": "...", "output": {...} }
```

| Enjin | Pemacu |用途 |
| --- | --- | --- |
| `simulated` | Tiada GPU | Manifest deterministik untuk demo & ujian |
| `metahuman` | HTTP | MetaHuman / renderer MetaHuman tersuai |
| `http` | HTTP | Renderer 3D tersuai |

Konfigurasi: `config/blive.php` -> `engines`, dan env `BLIVE_AVATAR_ENGINE`.

---

## 7. Penstriman

Setiap platform mempunyai encoder profile, credential, bitrate dan status
sendiri - satu stream tidak dihantar secara membuta tuli ke semua platform.

| Platform | Mod | Status |
| --- | --- | --- |
| YouTube Live | `official_api` + RTMP | Aktif |
| Facebook Live | `graph_api` + RTMPS | Aktif |
| TikTok LIVE | `manual_rtmp` | Tidak aktif (perlu kelayakan) |

**Nota TikTok**: Akses LIVE API memerlukan kelayakan dan proses kelulusan
berkaitan. Mod RTMP manual disediakan sebagai fallback.

**Rahsia**: Stream key dan token OAuth tidak disimpan dalam plaintext. Medan
`credential_reference` hanya menyimpan rujukan (contoh `vault://youtube/abc`).

---

## 8. Pemisahan Tenant

Semua jadual membawa `tenant_id` dan menggunakan trait `BelongsToTenant`.

> ⚠️ **Perisai lapisan servis**: trait tersebut mengekalkan global scope pada
> QUERY sahaja. Jika model dimuatkan semasa tenant A dan `update()` dipanggil
> semasa tenant B aktif, trait itu tidak menolak. Oleh itu setiap operasi
> tulis mengesahkan pemilikan rekod terlebih dahulu
> (`LiveControlService::milikTenant()`, `LiveSessionService::mula()/henti()`).

---

## 9. Konfigurasi

| Env | Kegunaan | Default |
| --- | --- | --- |
| `BLIVE_AVATAR_ENGINE` | Enjin avatar lalai | `simulated` |
| `BLIVE_METAHUMAN_URL` | Endpoint renderer MetaHuman | - |
| `BLIVE_METAHUMAN_KEY` | Kunci renderer | - |
| `BLIVE_VOICE_PROVIDER` | Sumber suara | `voice_studio` |
| `BLIVE_VOICE_LOCALE` | Locale lalai | `ms-MY` |
| `BLIVE_FFMPEG` | Binari FFmpeg | `ffmpeg` |
| `BLIVE_ENABLE_FFMPEG` | Benarbenar jalankan FFmpeg | `false` |

Had lajur dalam `config/blive.php` -> `limits`:
avatar setiap syarikat, sesi serentak, aksara ucapan, masa sesi.

---

## 10. Ujian

```bash
php artisan test --filter BLiveTest
```

40 ujian, 134(assertions). Liputan: kitaran hidup avatar, consent gate,
kawalan hybrid, keselamatan AI Brain, penstriman multi-platform, pemisahan
tenant, dan API HTTP.

---

## 11. Batasan Semasa

Fasa 1-2. Belum disertakan:

- Worker render FFmpeg sebenar (gateway hanya mengesahkan konfigurasi).
- Integrasi API platform sebenar (YouTube/Facebook/TikTok).
- Pipeline audio -> lip sync -> facial motion.
- Pengesanan soalan penonton secara langsung daripada webhook platform.
- Modul billing / token wallet khusus (kredit dikongsi dengan AIGateway).
