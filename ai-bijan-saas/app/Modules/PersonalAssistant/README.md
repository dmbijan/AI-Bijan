# B-Personal AI Assistant (BAB 17 + 17A)

Pembantu AI peribadi untuk setiap pemilik bisnes, pengurus, pekerja dan pengguna yang dibenarkan. Bukan sekadar chatbot — ia ruang kerja pintar yang memahami konteks perniagaan.

## 9 Jenis Assistant

| # | Jenis | Kunci | Untuk |
|---|-------|-------|-------|
| 1 | Owner AI | `owner` | Pemilik/Pengarah — ringkasan bisnes, cash-flow, risiko |
| 2 | Executive AI | `executive` | Pengurusan kanan — briefing, KPI, meeting prep |
| 3 | Sales AI | `sales` | Team jualan — lead, follow-up, quotation, pipeline |
| 4 | Marketing AI | `marketing` | Marketing — kempen, segmentasi, kandungan, SEO |
| 5 | Operations AI | `operations` | Operasi — task, workflow, inventori, vendor |
| 6 | Finance AI | `finance` | Kewangan — invois, cash-flow, reconciliation |
| 7 | HR AI | `hr` | HR — onboarding, shift, cuti, polisi Q&A |
| 8 | Support AI | `support` | Sokongan — FAQ, tiket, reply draft, eskalasi |
| 9 | Industry AI | `industry` | Khusus industri (Event, Retail, F&B, Fleet, Salon, Construction, Property, Professional) |

## Struktur Modul

```
app/Modules/PersonalAssistant/
├── PersonalAssistantServiceProvider.php
├── config/
│   └── personal_assistant.php      # 9 assistant, memory types, alert types
├── Models/
│   ├── PersonalTask.php            # State machine tugas AI
│   ├── MemoryItem.php              # Item memori (5 jenis)
│   └── UserPreference.php          # Preferensi per pengguna
├── Services/
│   ├── AssistantService.php        # Senarai, cadangan, preferensi, tukar model
│   ├── TaskExecutor.php            # State machine execution
│   ├── ContextBuilderService.php   # Gabung konteks untuk AI Gateway
│   ├── MemoryService.php           # CRUD memori + PDPA purge
│   └── ProactiveAlertService.php   # Alert proaktif
└── Http/Controllers/
    └── PersonalAssistantController.php
```

## Memory Architecture (BAB 17A)

**Prinsip utama:** Memory (persistent) ≠ AI Model (swappable). Tukar model tanpa hilang data.

| Jenis Memori | Storage | Skop |
|--------------|---------|------|
| Personal preferences | `ai_user_preferences` | User |
| Work context | `ai_memory_items` | User + permission |
| Business profile | `ai_business_profiles` | Tenant |
| Operational records | Modul sumber | Permission-based |
| Conversation history | `ai_conversations` | User |
| Approved knowledge | Qdrant (vector) | Tenant + classification |
| Derived insights | `ai_derived_insights` | Data source access |

### Memory Rules

- Simpan hanya maklumat yang mempunyai tujuan jelas
- Sediakan review, correction dan deletion
- Jangan jadikan andaian AI sebagai fakta
- Rekod sumber dan masa untuk fakta penting
- Hormati permission asal semasa retrieval
- Jangan guna data tenant lain
- Bezakan fakta / inferens / ramalan / cadangan
- Data rasmi dari source of truth, bukan memori AI

## Task Execution State Machine (BAB 17.5)

```
Draft → Planned → Awaiting Approval → Queued → Running
                                                  ↓
                        Completed / Partially Completed / Failed / Cancelled
```

Peralihan tidak sah akan ditolak dengan `RuntimeException`. Tugas berisiko tinggi (`high`/`critical`) tidak boleh di-queue tanpa kelulusan.

### Risk Levels

| Tahap | Contoh | Kawalan |
|-------|--------|---------|
| `low` | Ringkas laporan | Permission + audit |
| `moderate` | Cipta task atau draft | Validation + log |
| `high` | Hantar komunikasi luaran | Policy + approval |
| `critical` | Refund, payout, komitmen kewangan | Strong auth + approval |

## Context Builder (BAB 17A.5)

Menggabungkan konteks sebelum hantar ke AI Gateway:

```php
$context = $contextBuilder->buildContext(
    $tenantId, $userId, 'owner', 'Semak invois tertunggak'
);

// Output:
// - preferences (bahasa, tone, format)
// - business_profile (nama, industri, produk)
// - conversation_history (20 terakhir)
// - memory_items (30 teratas, verified dahulu)
// - active_insights (10 terakhir)
// - documents (Qdrant — bila tersedia)
// - metadata (estimated_tokens, max_context_tokens)
```

## Proactive Alerts (BAB 17.6)

Setiap alert menunjukkan 7 perkara:

1. Apa yang berlaku
2. Data/rekod asas
3. Tahap kepastian
4. Kesan mungkin
5. Tindakan dicadangkan
6. Sama ada boleh automatik
7. Cara meluluskan/menolak/membetulkan

### Jenis Alert

| Jenis | Pemicu | Keutamaan |
|-------|--------|-----------|
| `invoice_overdue` | Invois lepas tarikh matang | high |
| `lead_not_contacted` | Lead tidak dihubungi >3 hari | medium |
| `quotation_no_response` | Quotation tiada jawapan >7 hari | medium |
| `ai_usage_limit` | Penggunaan AI >80% | medium |
| `stock_insufficient` | Stok dijangka tidak cukup | medium |
| `campaign_over_budget` | Kempen melebihi bajet | high |
| `integration_failure` | Integrasi payment bermasalah | critical |
| `sales_target_behind` | Sasaran jualan ketinggalan | medium |

## API Endpoints

Semua di bawah `/api/v1/personal-ai`.

### View (`personal_ai.view`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/assistants` | Senarai 9 assistant |
| GET | `/assistants/{key}` | Detail satu assistant |
| GET | `/recommend` | Cadangan ikut role pengguna |
| GET | `/preferences` | Preferensi pengguna |
| GET | `/memory` | Senarai item memori |
| GET | `/memory/summary` | Ringkasan memori |
| GET | `/tasks` | Senarai tugas |
| GET | `/tasks/{id}` | Detail tugas |
| GET | `/tasks-stats` | Statistik tugas |
| GET | `/alerts` | Alert proaktif |

### Tasks (`personal_ai.tasks`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/tasks` | Cipta tugas baru |
| POST | `/tasks/{id}/transition` | Peralihan status |
| POST | `/context` | Bina konteks AI |

### Memory (`personal_ai.memory`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/memory` | Simpan item memori |
| PUT | `/memory/{id}` | Kemas kini (review/correction) |
| DELETE | `/memory/{id}` | Padam item |
| POST | `/memory/purge` | PDPA — padam semua memori peribadi |

### Configure (`personal_ai.configure`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| PUT | `/preferences` | Kemas kini preferensi |
| POST | `/switch-model` | Tukar model AI (memori kekal) |

## Contoh Penggunaan

### Tukar Model Tanpa Hilang Memori

```bash
curl -X POST /api/v1/personal-ai/switch-model \
  -H "Authorization: Bearer {token}" \
  -d '{"model_id": "asai/claude-opus-5"}'
```

Respons:
```json
{
  "success": true,
  "data": {
    "previous_model": "asai/gpt-6-astra",
    "current_model": "asai/claude-opus-5",
    "memory_preserved": true,
    "message": "Model ditukar. Semua memori, profil bisnes dan sejarah perbualan kekal sama."
  }
}
```

### Cipta & Jalankan Tugas

```bash
# 1. Cipta tugas
curl -X POST /api/v1/personal-ai/tasks \
  -d '{"objective":"Semak invois tertunggak","assistant_type":"finance","risk_level":"low"}'

# 2. Rancang
curl -X POST /api/v1/personal-ai/tasks/{id}/transition \
  -d '{"action":"plan"}'

# 3. Queue & jalankan
curl -X POST /api/v1/personal-ai/tasks/{id}/transition -d '{"action":"queue"}'
curl -X POST /api/v1/personal-ai/tasks/{id}/transition -d '{"action":"start"}'

# 4. Selesaikan
curl -X POST /api/v1/personal-ai/tasks/{id}/transition \
  -d '{"action":"complete","result":{"summary":"5 invois tertunggak","reference":"INV-SUM-001"}}'
```

## Pematuhan PDPA

`purgeMyMemory()` memadam:
- Item memori peribadi
- Sejarah perbualan peribadi
- Preferensi pengguna

**Tidak dipadam** (keperluan undang-undang):
- Rekod kewangan & invois (Akta Akuntansi 2013)
- Rekod payroll & statutori
- Rekod e-Invois LHDN

## Nota Implementasi

- **Qdrant vector search** belum disambung (`include_vector_search` = false)
- **AI Gateway** belum disambung — tiada panggilan model sebenar
- **Tool execution** belum diimplementasi
- Handler alert bergantung pada jadual modul lain (`invoices`, `crm_leads`, `ai_credit_wallets`)
