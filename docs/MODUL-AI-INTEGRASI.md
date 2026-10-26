# Modul AI — Panduan Integrasi

> **Dikemas kini:** 26 Oktober 2026
> Dokumen ini menerangkan bagaimana modul AI AI BIJAN berinteraksi.
>
> **Modul AI teras (5 asal):** `AIGateway`, `AIAgents`, `PersonalAssistant`, `Boardroom`, `FlowAutomation`
> **Ditambah kemudian:** `ExecutiveAI` (BAB 82), `AgentStudio` + `McpStudio` (BAB 85),
> `BusinessIntel` (BAB 86), **`ADE`** (BAB 92)
>
> **Peraturan tidak berubah:** Tiada modul boleh memanggil provider AI secara terus.
> Semua mesti melalui `AIGateway\Services\GatewayService`.

## Peta Kebergantungan

```
                    ┌─────────────────────┐
                    │   B-AI Gateway      │  (BAB 21 + 22)
                    │  Model + Wallet     │
                    └──────────┬──────────┘
                               │ semua panggilan model AI
              ┌────────────────┼────────────────┐
              ▼                ▼                ▼
    ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
    │ B-AI Agents  │  │ B-Personal   │  │  B-Boardroom │
    │   (BAB 16)   │  │ AI (BAB 17)  │  │   (BAB 20)   │
    │  11 ejen     │  │ 9 assistant  │  │  7 langkah   │
    └──────┬───────┘  └──────┬───────┘  └──────┬───────┘
           │                 │                 │
           └─────────────────┼─────────────────┘
                             ▼
                    ┌─────────────────────┐
                    │  B-Flow Automation  │  (BAB 28)
                    │   Trigger → Action  │
                    └─────────────────────┘
```

**Peraturan:** Tiada modul boleh memanggil provider AI secara terus. Semua mesti melalui `AIGateway\Services\GatewayService`.

### Modul AI Lanjutan (ditambah selepas dokumen asal)

```
                    ┌─────────────────────────┐
                    │   B-AI Gateway          │
                    │  Model + Wallet + Guard │
                    └───────────┬─────────────┘
                                │
     ┌──────────────┬───────────┼───────────┬──────────────┐
     ▼              ▼           ▼           ▼              ▼
┌─────────┐  ┌───────────┐ ┌─────────┐ ┌──────────┐ ┌────────────┐
│AgentStu │  │McpStudio  │ │ExecAI   │ │Business  │ │   ADE      │
│dio      │  │(MCP)      │ │(L0-L4)  │ │Intel     │ │(A1-A4)     │
│16 templ │  │tools/res  │ │briefing │ │learning  │ │skills +    │
│4 auton  │  │allowlist  │ │video    │ │loop      │ │sandbox +   │
│         │  │HITL       │ │         │ │competitor│ │coordinator │
└─────────┘  └───────────┘ └─────────┘ └──────────┘ └────────────┘
```

**ADE (BAB 92)** ialah lapisan **pembangunan** — ia menggunakan `AgentStudio`/`AIGateway`
sebagai executor untuk sub-ejen (implementer/tester/reviewer/researcher).
Sambungan executor masih **P2** — ADE kini berfungsi dalam mod dry-run deterministik.

## Aliran Contoh: Pemilik Tanya Soalan Strategik

```
1. Pemilik → POST /api/v1/boardroom/sessions
   {question: "Patutkah kita tambah staf jualan?"}

2. Boardroom → detectCategory() → "financial"
   → selectAgents() → [cfo, ceo, ciso]

3. Boardroom → distributeContext() — sanitasi dahulu
   → setiap ejen terima konteks minimum

4. Untuk setiap ejen:
   Boardroom → AIAgents\AgentOrchestrator::executeAgent()
   → AgentGovernance::validateAction()  (6 peraturan)
   → AIGateway\GatewayService::call()
       → PiiRedactor::redactMessages()
       → routeModel("reasoning") → GPT-6 Astra
       → wallet->canAfford() ? panggil : tolak
       → AiUsageEvent log

5. Boardroom → recordFinding() — wajib jenis + sumber
   → detectConflicts() — divergensi skor > 0.3
   → prepareSummary() — susun fakta/anggaran/andaian
   → status = awaiting_owner_decision

6. Pemilik → POST /decide {decision: "approve"}
   → status = decided
```

## Aliran Contoh: Automasi Invois Tertunggak

```
1. Scheduler → FlowAutomation\FlowEngine::triggerEvent()
   atau triggerManual()

2. FlowEngine:
   → kill_switch check
   → idempotency check (SHA-256)
   → rate limit check (100/jam)
   → create WorkflowRun

3. Untuk setiap langkah:
   ActionDispatcher::dispatch()
   ├── send_notification → placeholder (modul Notifikasi)
   ├── ai_task → AIAgents\AgentOrchestrator
   │              → AIGateway\GatewayService
   ├── update_record → DB sebenar
   ├── connector_api → HTTP sebenar + retry
   └── request_approval → status waiting_approval

4. Jika gagal → markFailed() → retry 30s/120s/600s
   → selepas 3× → dead_letter
```

## Perkongsian Jadual

| Jadual | Pemilik | Pembaca |
|--------|---------|---------|
| `ai_conversations` | AIAgents (AgentMemoryManager) | PersonalAssistant (ContextBuilder) |
| `ai_business_profiles` | AIAgents | PersonalAssistant (ContextBuilder) |
| `ai_derived_insights` | AIAgents | PersonalAssistant |
| `ai_model_catalog` | AIGateway | AIAgents, PersonalAssistant, Boardroom |
| `ai_credit_wallets` | AIGateway | PersonalAssistant (alert), FlowAutomation |
| `ai_usage_events` | AIGateway | semua (metering) |
| `approval_requests` | Core (platform) | AIAgents, FlowAutomation |

> **Peraturan:** Modul bukan-pemilik hanya **baca**. Tulisan mesti melalui servis pemilik.

## Pengasingan Tenant

Setiap jadual AI mempunyai `tenant_id`. Setiap query mesti di-skop:

```php
->where('tenant_id', $tenantId)
```

Atau guna scope model:
```php
Agent::forTenant($tenantId)
WorkflowRun::forTenant($tenantId)
BoardroomSession::forTenant($tenantId)
```

**Ujian wajib:** Tenant A tidak boleh baca data Tenant B melalui mana-mana endpoint.

## Titik Integrasi — Status (disemak 26 Okt 2026)

| Titik | Status | Tindakan |
|-------|--------|----------|
| `GatewayService::call()` dari AgentOrchestrator | ✅ Siap | `invokeAgent()` |
| `GatewayService::call()` dari Boardroom findings | ⏸️ Belum | Jana dapatan AI sebenar |
| `FlowEngine` action `ai_task` | ⏸️ Placeholder | Panggil AgentOrchestrator |
| `ActionDispatcher` send_notification | ⏸️ Placeholder | Sambung modul Notifikasi |
| Qdrant vector search | 🟡 **Hash-v1 siap** | `EmbeddingService` + `VectorStoreService` (256 dim, PHP kosinus). Qdrant luaran belum |
| Context Builder → Gateway | ✅ **Siap** | `ContextBuilderService::vectorSearch()` dengan graceful degradation |
| `ADE` executor → `AgentStudio` | ⏸️ **P2** | Callback disediakan (`jalankan($t,$id,?callable)`); sambungan enjin belum |
| `AgentStudio` → `AIGateway` | ✅ Siap | `AgentBlueprintService::lancarkanEjen()` |
| `McpStudio` → `AIGateway` | ✅ Siap | MCP tool call melalui gateway |
| `BusinessIntel` learning loop → skill | ⏸️ **P2** | Skill auto-jana daripada pattern belum |

## Integrasi Siap: Ejen → Gateway

`AgentOrchestrator::invokeAgent()` menyambung ejen ke AI Gateway:

```php
POST /api/v1/ai-agents/invoke
{
  "agent_key": "cfo",
  "objective": "Analisis aliran tunai bulan ini",
  "context": {"period": "2026-10"}
}
```

Aliran dalaman:
```
invokeAgent(tenantId, agentKey, objective, context)
  ├── Auto-konfigurasi ejen jika belum wujud (dari config)
  ├── AgentGovernance::validateAction()  → 6 peraturan
  ├── Bina messages: system_prompt + konteks + objective
  ├── mapAgentToTaskType() → routing model
  │     ceo/cfo/coo/ciso/analyst/researcher → reasoning
  │     cmo/ads_specialist                  → creative
  │     chro/support/exec_assistant         → quick
  ├── GatewayService::call()  → PII redact, wallet, model
  └── AgentGovernance::logAudit()  → jejak audit
```

## Ujian (68 ujian baharu)

| Fail | Ujian | Skop |
|------|-------|------|
| `ModulAiTenantIsolationTest` | 7 | Pengasingan tenant merentas 5 modul |
| `PersonalAiTaskStateTest` | 10 | State machine, risk level, approval gate |
| `BoardroomWorkflowTest` | 15 | 7 langkah, sanitasi konteks, konflik, keputusan |
| `FlowAutomationTest` | 14 | Idempotency, kill switch, retry, dead-letter |
| `AiGatewayTest` | 22 | Kiraan kredit, PII, wallet, simulasi |

> **Nota:** Ujian memerlukan `composer install` + pangkalan data ujian. Jalankan:
> ```bash
> php artisan test --filter="AiGatewayTest|BoardroomWorkflowTest|FlowAutomationTest|PersonalAiTaskStateTest|ModulAiTenantIsolationTest"
> ```

## Scheduler Berdaftar (`routes/console.php`)

| Nama | Jadual | Tugas |
|------|--------|-------|
| `helpdesk-sla` | 5 minit | Semak SLA tiket (sedia ada) |
| `flow-retries` | 1 minit | Proses run gagal yang due |
| `ai-wallet-reset` | Bulanan (1hb 00:05) | Reset kuota kredit |
| `ai-wallet-alerts` | Harian (08:00) | Log alert baki rendah |

Semua scheduler ikut corak `TenantContext::set($tenant)` → proses → `clear()`.

## Langkah Seterusnya (Keutamaan)

1. **Sambung GatewayService ke AgentOrchestrator** — supaya ejen benar-benar menjana jawapan
2. **Daftar scheduler** — `processRetries()` + reset kuota wallet bulanan
3. **Setup Qdrant** — vector search untuk knowledge base
4. **Sambung notifikasi** — `send_notification` action dalam Flow
5. **Tulis ujian** — tenant isolation, state machine, governance rules
6. **Tambah halaman Filament** — admin panel untuk katalog model & usage

## Konfigurasi `.env`

```env
# AI Gateway (BAB 21)
AI_GATEWAY_URL=https://serveras.click/v1
AI_GATEWAY_KEY=asai_sk_xxxxxxxxxxxx
AI_GATEWAY_TIMEOUT=120
AI_GATEWAY_LIVE=false   # true untuk panggilan sebenar
```

> **JANGAN** commit `AI_GATEWAY_KEY` ke git.
