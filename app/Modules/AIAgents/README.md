# B-AI Agents — Multi-Agent System (BAB 16)

Modul ejen AI berbilang untuk AI BIJAN. Menyediakan 11 ejen khusus dengan tools, budget limits, approval matrix, governance dan memory persistence.

## Gambaran Keseluruhan

Modul ini ialah **jantung sistem AI-Bijan** — semua ejen AI yang beroperasi dalam platform didaftarkan, dikonfigurasi dan diaudit di sini.

### 11 Ejen

| Ejen | Peranan | Tools Utama |
|------|---------|-------------|
| `ceo` | Chief of Staff — koordinasi & ringkasan eksekutif | `create_task`, `assign_agent`, `summarize_workspace` |
| `exec_assistant` | Kalendar, e-mel, nota, susulan | `calendar.read`, `email.draft`, `task.create` |
| `cmo` | Strategi pemasaran, kandungan, kempen | `campaign.draft`, `ads.read`, `content.generate` |
| `cfo` | Aliran tunai, bajet, invois, risiko | `finance.read`, `report.generate`, `invoice.draft` |
| `coo` | SOP, operasi, inventori, kapasiti | `inventory.read`, `task.assign`, `operations.report` |
| `ciso` (CSO) | Leads, pipeline jualan, affiliate | `crm.read`, `lead.update`, `sales.report` |
| `chro` | Kakitangan, shift, cuti, onboarding | `hr.read`, `shift.draft`, `leave.review` |
| `support` | FAQ, pesanan pelanggan, eskalasi | `knowledge.search`, `conversation.reply_draft` |
| `ads_specialist` | Google/Meta Ads, optimasi kempen | `ads.performance.read`, `campaign.draft` |
| `researcher` | Trend, pesaing, sumber maklumat | `web.research`, `competitor.compare` |
| `analyst` | KPI, ramalan, insight | `analytics.query`, `forecast.generate` |

## Struktur Modul

```
app/Modules/AIAgents/
├── AIAgentsServiceProvider.php       # Daftar binding + routes + config
├── config/
│   └── ai_agents.php                 # 11 ejen, model routing, approval matrix
├── Models/
│   └── Agent.php                     # Eloquent model
├── Services/
│   ├── AgentService.php              # CRUD + konfigurasi ejen
│   ├── AgentOrchestrator.php         # Multi-agent workflow
│   ├── ToolExecutor.php              # Eksekusi tool + validasi
│   ├── AgentMemoryManager.php        # Memory persistence
│   └── AgentGovernance.php           # Governance enforcement
└── Http/Controllers/
    └── AIAgentController.php         # API endpoints
```

## Pemasangan

### 1. Migration

```bash
php artisan migrate
```

Jadual yang dicipta:
- `ai_agents` — konfigurasi ejen per tenant
- `ai_agent_sessions` — sesi multi-agent
- `ai_agent_executions` — eksekusi individu
- `ai_tool_executions` — log panggilan tool
- `ai_conversations` — sejarah perbualan
- `ai_business_profiles` — profil bisnes (konteks memori)
- `ai_derived_insights` — insight yang dipelajari
- `ai_governance_audits` — audit trail governance

### 2. Service Provider

Didaftarkan dalam `bootstrap/providers.php`:

```php
\App\Modules\AIAgents\AIAgentsServiceProvider::class,
```

### 3. Permissions

Permissions dalam `config/aibijan.php`:

| Permission | Keterangan |
|------------|------------|
| `ai_agents.view` | Lihat senarai ejen, stats, governance report |
| `ai_agents.manage` | Konfigurasi, enable/disable, reset ejen |
| `ai_agents.execute` | Jalankan tugas ejen & tool calls |

## API Endpoints

Semua endpoint di bawah `/api/v1/ai-agents` dengan auth Sanctum + tenant middleware.

### View (`ai_agents.view`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/list` | Senarai 11 ejen tersedia |
| GET | `/{agentKey}` | Detail ejen dari config |
| GET | `/tenant/list` | Ejen yang dikonfigurasi untuk tenant |
| GET | `/{agentKey}/stats` | Statistik eksekusi |
| POST | `/recommend` | Cadangan ejen ikut industri |
| GET | `/business-profile` | Profil bisnes untuk konteks AI |
| POST | `/business-profile` | Simpan/kemas kini profil bisnes |
| GET | `/{agentKey}/conversations` | Sejarah perbualan |
| POST | `/conversation/save` | Simpan perbualan |
| GET | `/governance/report` | Laporan pelanggaran governance |

### Manage (`ai_agents.manage`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/{agentKey}/configure` | Konfigurasi ejen |
| PUT | `/{agentKey}/update` | Kemas kini konfigurasi |
| POST | `/{agentKey}/disable` | Nyahaktifkan ejen |
| POST | `/{agentKey}/enable` | Aktifkan ejen |
| POST | `/{agentKey}/reset` | Reset ke default |

### Execute (`ai_agents.execute`)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/execute/task` | Jalankan tugas ejen (dengan governance check) |
| POST | `/execute/tool` | Panggil tool secara terus |

## Model Routing

Model dipilih mengikut jenis tugas (`config/ai_agents.php`):

| Task Type | Model |
|-----------|-------|
| `reasoning` | GPT-6 Astra / Claude Opus |
| `coding` | DeepSeek V4 Pro |
| `creative` | Muse Wonder |
| `quick` | GLM Flash |
| `long_context` | Kimi K3 |
| `multilingual` | Qwen 3.8 Max |
| `default` | MiniMax M3 |

## Approval Matrix

Tindakan sensitif memerlukan kelulusan manusia:

| Tindakan | Tahap | Kelulusan |
|----------|-------|-----------|
| `financial_commitment` | L0 | tenant_owner |
| `contract_signing` | L0 | tenant_owner |
| `hiring_decision` | L0 | tenant_owner |
| `payment_approve` | L0 | tenant_owner |
| `journal_post` | L1 | department_manager |
| `purchase_order` | L1 | department_manager |
| `campaign_publish` | L1 | department_manager |
| `refund_over_50` | L1 | department_manager |

## Governance Rules

`AgentGovernance::validateAction()` memeriksa 6 peraturan sebelum eksekusi:

1. **Least privilege** — ejen hanya boleh akses tool yang dibenarkan
2. **Tool allowlist** — tindakan mesti dalam senarai tool ejen
3. **Parameter validation** — tiada data sensitif bocor (password, API key, dll)
4. **Budget limits** — had kos harian per ejen
5. **Sensitive action approval** — tindakan kritikal perlu kelulusan manusia
6. **Data isolation** — ejen mesti milik tenant yang betul

## Memory Architecture (BAB 17A)

`AgentMemoryManager` menyimpan 6 jenis memori:

| Jenis | Storage | Tujuan |
|-------|---------|--------|
| Business Profile | `ai_business_profiles` | Konteks asas bisnes |
| Conversation History | `ai_conversations` | Sejarah perbualan |
| Derived Insights | `ai_derived_insights` | Trend, risiko, cadangan |
| Vector Store | Qdrant (luaran) | Semantic search dokumen |
| Knowledge Base | R2/S3 (luaran) | Fail & dokumen |
| System Prompts | `config/ai_agents.php` | Template prompt per role |

Prinsip: **Memory (persistent) ≠ AI Model (swappable)** — tukar model tanpa hilang data.

## Contoh Penggunaan

### Konfigurasi Ejen

```bash
curl -X POST /api/v1/ai-agents/cfo/configure \
  -H "Authorization: Bearer {token}" \
  -d '{
    "system_prompt": "Anda ialah CFO yang teliti...",
    "budget_limits": {"max_cost_per_run": 0.50},
    "tools": ["finance.read", "report.generate"]
  }'
```

### Jalankan Tugas

```bash
curl -X POST /api/v1/ai-agents/execute/task \
  -H "Authorization: Bearer {token}" \
  -d '{
    "agent_key": "cfo",
    "task_type": "reasoning",
    "objective": "Analisis aliran tunai bulan ini",
    "input": {"period": "2026-10"}
  }'
```

Respons jika governance gagal:

```json
{
  "success": false,
  "message": "Governance validation failed.",
  "violations": ["Sensitive action requires human approval."]
}
```

## Nota Implementasi

- **Tool handlers** dalam `ToolExecutor` ialah stub. Implementasi sebenar perlu memanggil servis modul berkaitan.
- **AI Gateway** (serveras.click) belum disambung — lihat B-AI Gateway (BAB 21).
- **Vector store** (Qdrant) belum disambung — perlu setup berasingan.
- **Model calls** belum diimplementasi — semua eksekusi kini placeholder.
