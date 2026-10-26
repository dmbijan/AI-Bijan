# B-AI Gateway (BAB 21 + 22)

Satu pintu untuk semua panggilan model AI melalui **serveras.click**. Mengandungi model catalog, routing, usage metering, credit wallet, PII redaction, circuit breaker dan guardrails.

## Prinsip Keselamatan

> **API keys adalah ADMIN ONLY — TIDAK PERNAH didedahkan kepada pelanggan.**
> Pelanggan hanya berinteraksi melalui UI/platform AI BIJAN. Semua panggilan dibuat dari backend menggunakan admin key tersembunyi.

## Struktur Modul

```
app/Modules/AIGateway/
├── AIGatewayServiceProvider.php
├── config/ai_gateway.php            # Provider, routing, tiers, guardrails
├── Models/
│   ├── AiModel.php                  # Katalog model + kiraan kredit
│   ├── AiCreditWallet.php           # Wallet kredit per tenant
│   └── AiUsageEvent.php             # Log penggunaan
├── Services/
│   ├── GatewayService.php           # Panggilan provider + circuit breaker
│   ├── WalletService.php            # Top-up, deduct, ledger, ringkasan
│   ├── ModelCatalogService.php      # CRUD katalog model
│   └── PiiRedactor.php              # Redaksi data sensitif
└── Http/Controllers/
    └── AIGatewayController.php
```

## Model Routing (Automatik)

Dipilih mengikut jenis tugas:

| Task Type | Model | Tier |
|-----------|-------|------|
| `reasoning` | GPT-6 Astra | standard |
| `deep_reasoning` | Claude Opus 5 | premium |
| `coding` | DeepSeek V4 Pro | standard |
| `creative` | Muse Wonder 1.0 | standard |
| `quick` | GLM 5.3 Flash | flash |
| `long_context` | Kimi K3 | ultra |
| `multilingual` | Qwen 3.8 Max | standard |
| `realtime` | Grok 4.7 | — |
| `balanced` / `default` | MiniMax M3 | flash |

## Model Tier & Multiplier (BAB 22.5)

```
credits = (input_tokens × input_rate) + (output_tokens × output_rate)
input_rate = base_rate × tier_multiplier
```

| Tier | Multiplier | Model | Terbaik Untuk |
|------|------------|-------|---------------|
| Flash | 1.0× | GLM Flash, MiniMax M3 | Chat ringkas, jawapan pantas |
| Standard | 1.5× | Qwen, GPT-6, DeepSeek | Coding, analisis, multilingual |
| Premium | 2.0× | Claude Opus 4.8/5 | Penaakulan kompleks |
| Ultra | 2.5× | Claude 5.5, Kimi K3 | Konteks panjang, tugas lanjutan |

**Peraturan kredit:**
- Bulat ke atas ke nearest **100**
- Retry dalam 5 minit = **percuma**
- Cache hit = **percuma**
- Imej: RM0.04–0.12 setiap imej
- Audio: 1 kredit / 5 saat
- Video: 1 kredit / 2 saat

## Wallet (BAB 22)

Dua sumber kredit:

1. **Kuota pelan** (`period_quota`) — reset setiap bulan
2. **Top-up balance** (`balance`) — expire 30 hari

Penggunaan ditolak dari kuota dahulu, kemudian balance.

Alert automatik pada penggunaan **80%, 90%, 100%**.

## Guardrails

| Kawalan | Nilai Default |
|---------|---------------|
| Kos maks per permintaan | RM5.00 |
| Token maks per permintaan | 200,000 |
| Permintaan/minit per tenant | 60 |
| Circuit breaker | 5 kegagalan → cooldown 60s |
| Fallback model | GLM Flash |

## PII Redaction

Sebelum prompt dihantar ke provider luar, data berikut diredaksi:

| Jenis | Corak |
|-------|-------|
| E-mel | `user@example.com` → `[REDACTED]` |
| Telefon MY | `+60123456789` → `[REDACTED]` |
| Kad kredit | 13–19 digit → `[REDACTED]` |
| No. IC | `900101-01-1234` → `[REDACTED]` |

Selain itu, kunci sensitif (`password`, `api_key`, `token`, `secret`, `credential`, `ssn`, `credit_card`) diredaksi sepenuhnya.

## API Endpoints

Semua di bawah `/api/v1/ai-gateway`.

### Use (`ai_gateway.use`)

| Method | Endpoint |
|--------|----------|
| POST | `/chat` |

### Models (`ai_gateway.models`)

| Method | Endpoint |
|--------|----------|
| GET | `/models` — senarai (tanpa ID dalaman) |
| GET | `/metadata` — provider, routing, tiers |

### Wallet (`ai_gateway.wallet`)

| Method | Endpoint |
|--------|----------|
| GET | `/wallet` |
| GET | `/wallet/ledger` |
| GET | `/usage/summary` |
| POST | `/wallet/top-up` |

### Admin (`ai_gateway.admin`)

| Method | Endpoint |
|--------|----------|
| GET | `/admin/models` — dengan ID dalaman + health |
| POST | `/admin/models` — upsert |
| POST | `/admin/models/{id}/toggle` |
| GET | `/admin/usage/by-model` |
| GET | `/admin/usage/events` |

## Contoh Penggunaan

### Chat

```bash
curl -X POST /api/v1/ai-gateway/chat \
  -H "Authorization: Bearer {token}" \
  -d '{
    "messages": [
      {"role": "system", "content": "Anda pembantu bisnes."},
      {"role": "user", "content": "Ringkaskan prestasi jualan bulan ini."}
    ],
    "task_type": "reasoning",
    "max_output_tokens": 500
  }'
```

Respons:
```json
{
  "success": true,
  "content": "...",
  "model": "GPT-6 Astra",
  "usage": {
    "input_tokens": 45,
    "output_tokens": 320,
    "total_tokens": 365,
    "credits_charged": 100,
    "latency_ms": 1840
  },
  "correlation_id": "01JA..."
}
```

Jika baki tidak mencukupi:
```json
{
  "success": false,
  "error": "Baki kredit AI tidak mencukupi. Sila top-up.",
  "correlation_id": "01JA..."
}
```

### Wallet

```bash
curl /api/v1/ai-gateway/wallet
```

```json
{
  "success": true,
  "data": {
    "balance": 150.00,
    "period_quota": 800.00,
    "used_this_period": 620.00,
    "effective_balance": 330.00,
    "usage_percent": 77.5,
    "currency": "MYR"
  },
  "alerts": [
    {"level": 80, "message": "Penggunaan kredit AI telah mencapai 77.5% daripada kuota."}
  ]
}
```

### Top-up

```bash
curl -X POST /api/v1/ai-gateway/wallet/top-up \
  -d '{"amount": 79, "reference": "INV-2026-001", "description": "Pakej 80 juta token"}'
```

## Konfigurasi

Tambah dalam `.env`:

```env
AI_GATEWAY_URL=https://serveras.click/v1
AI_GATEWAY_KEY=asai_sk_xxxxxxxxxxxx
AI_GATEWAY_TIMEOUT=120
AI_GATEWAY_LIVE=false
```

> `AI_GATEWAY_LIVE=false` (default) → mod simulasi, tiada panggilan sebenar
> `AI_GATEWAY_LIVE=true` → panggilan sebenar ke serveras.click

## Aliran Panggilan

```
1. Feature flag check → simulasi jika dimatikan
         ↓
2. Route model ikut task_type
         ↓
3. Circuit breaker check
         ↓
4. PII redaction pada messages
         ↓
5. Semak had token
         ↓
6. Semak baki wallet
         ↓
7. Panggil provider (HTTP)
         ↓
8. Kira kredit sebenar
         ↓
9. Tolak kredit dari wallet
         ↓
10. Log usage event (correlation_id)
```

## Nota Implementasi

- **Mod simulasi** aktif secara default — selamat untuk pembangunan
- **Streaming** belum diimplementasi (`features.streaming_enabled` = false)
- **Tool calling** belum diimplementasi
- **Audit kelulusan** — `request_approval` dalam Flow Automation akan berbual dengan gateway
- **Katalog model** perlu diisi melalui admin API atau seeder sebelum penggunaan sebenar
