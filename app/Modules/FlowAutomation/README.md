# B-Flow Automation (BAB 28)

Enjin automasi tanpa kod. Workflow terdiri daripada **Trigger → Condition → Action → Approval → Retry → Audit**.

## Model Workflow

| Komponen | Keterangan |
|----------|------------|
| **Trigger** | Domain event, webhook, jadual, manual, atau perubahan rekod |
| **Condition** | Penapis, nilai, status, had, cabang |
| **Action** | API dalaman, connector, tugas AI, notifikasi, kemas kini rekod |
| **Approval** | Kelulusan manusia bila diperlukan |
| **Retry** | Retry terhad + backoff + dead-letter |
| **Audit** | Input, output, masa, pemilik, keputusan |

## Struktur Modul

```
app/Modules/FlowAutomation/
├── FlowAutomationServiceProvider.php
├── config/flow_automation.php       # Trigger types, operators, actions, limits
├── Models/
│   ├── WorkflowDefinition.php       # Definisi workflow + condition evaluator
│   └── WorkflowRun.php              # Satu eksekusi (state machine)
├── Services/
│   ├── FlowEngine.php               # Enjin: trigger, execute, retry, dry-run
│   └── ActionDispatcher.php         # Handler untuk setiap jenis tindakan
└── Http/Controllers/
    └── FlowAutomationController.php
```

## Trigger Types

| Jenis | Config | Kegunaan |
|-------|--------|----------|
| `domain_event` | `event_name` | Event perniagaan (OrderPaid, InvoiceIssued) |
| `webhook` | `signature_header`, `secret_ref` | HTTP masuk dari sistem luar |
| `schedule` | `cron_expression`, `timezone` | Cron / masa berulang |
| `manual` | `allowed_roles` | Butang/API pengguna |
| `record_change` | `table`, `operation` | Rekod dicipta/dikemas kini/dipadam |

## Condition Operators

`equals`, `not_equals`, `greater_than`, `greater_or_equal`, `less_than`, `less_or_equal`, `contains`, `not_contains`, `in`, `not_in`, `is_empty`, `is_not_empty`, `changed`

Contoh:
```json
"conditions": [
  {"field": "total_amount", "operator": "greater_than", "value": 1000},
  {"field": "status", "operator": "equals", "value": "paid"}
]
```

## Action Types

| Jenis | Perlu Kelulusan |
|-------|-----------------|
| `internal_api` | ❌ |
| `connector_api` | ❌ |
| `ai_task` | ❌ |
| `create_task` | ❌ |
| `send_notification` | ❌ |
| `send_external_message` | ✅ |
| `update_record` | ❌ |
| `financial_action` | ✅ |
| `request_approval` | ❌ |
| `webhook_out` | ❌ |

## Perlindungan (BAB 28.3)

| Perlindungan | Implementasi |
|--------------|--------------|
| **Idempotency** | `idempotency_key` (SHA-256) — run berulang dipulangkan sedia ada |
| **Kill switch** | Per-workflow + per-tenant — hentikan semua run baru |
| **Rate limit** | `max_runs_per_hour` (100) per workflow |
| **Action limit** | `max_actions_per_run` (50) |
| **Retry** | 3 cubaan, backoff 30s → 120s → 600s, kemudian `dead_letter` |
| **Dry run** | Simulasi tanpa kesan sampingan |
| **Audit** | Semua run dan langkah dilog dalam `steps_log` |
| **Tenant isolation** | Semua query di-skop `tenant_id` |

## Status Run

```
pending → running → completed
            ↓
     waiting_approval → running
            ↓
          failed → (retry) → running
            ↓
       dead_letter (selepas 3 retry)
       cancelled
```

## API Endpoints

Semua di bawah `/api/v1/flows`.

### View (`flow.view`)

| Method | Endpoint |
|--------|----------|
| GET | `/workflows` |
| GET | `/workflows/{id}` |
| GET | `/runs` |
| GET | `/runs/{id}` |
| GET | `/stats` |
| GET | `/metadata` |

### Manage (`flow.manage`)

| Method | Endpoint |
|--------|----------|
| POST | `/workflows` |
| PUT | `/workflows/{id}` |
| DELETE | `/workflows/{id}` |
| POST | `/workflows/{id}/dry-run` |
| POST | `/workflows/{id}/activate` |
| POST | `/workflows/{id}/deactivate` |

### Execute (`flow.execute`)

| Method | Endpoint |
|--------|----------|
| POST | `/workflows/{id}/trigger` |
| POST | `/runs/{id}/retry` |
| POST | `/runs/{id}/cancel` |
| POST | `/process-retries` |

### Kill Switch (`flow.kill_switch`)

| Method | Endpoint |
|--------|----------|
| POST | `/workflows/{id}/kill-switch` |
| POST | `/tenant-kill-switch` |

## Contoh: Peringatan Invois Tertunggak

### Cipta workflow

```bash
curl -X POST /api/v1/flows/workflows \
  -d '{
    "name": "Peringatan Invois Tertunggak",
    "trigger_type": "schedule",
    "trigger_config": {"cron_expression": "0 9 * * *", "timezone": "Asia/Kuala_Lumpur"},
    "conditions": [
      {"field": "days_overdue", "operator": "greater_than", "value": 7}
    ],
    "steps": [
      {"type": "send_notification", "config": {"channel": "whatsapp", "recipient": "{{customer.phone}}", "message": "Invois {{invoice_number}} telah lewat {{days_overdue}} hari."}},
      {"type": "create_task", "config": {"table": "tasks", "data": {"title": "Follow up invois {{invoice_number}}", "tenant_id": "{{tenant_id}}"}}}
    ]
  }'
```

### Dry run (WAJIB sebelum aktifkan)

```bash
curl -X POST /api/v1/flows/workflows/{id}/dry-run \
  -d '{"payload":{"days_overdue":10,"customer":{"phone":"+60123456789"}}}'
```

Respons menunjukkan setiap langkah dan sama ada ia perlukan kelulusan.

### Aktifkan

```bash
curl -X POST /api/v1/flows/workflows/{id}/activate
```

### Cetus manual (ujian)

```bash
curl -X POST /api/v1/flows/workflows/{id}/trigger \
  -d '{"payload":{"days_overdue":10}}'
```

## Interpolasi Template

Placeholder `{{key}}` diganti dengan nilai payload:

```json
{"message": "Invois {{invoice_number}} lewat {{days_overdue}} hari"}
```

Menyokong nested key: `{{customer.phone}}`, `{{order.items.0.name}}`

## Contoh Workflow Tersedia (config)

| Workflow | Trigger | Langkah |
|----------|---------|---------|
| Pesanan Shopee Diterima | `webhook: shopee.order.created` | verify_signature → create_order → decrement_stock → generate_invoice → notify_customer |
| Peringatan Invois Tertunggak | `schedule: 0 9 * * *` | send_reminder → create_followup_task |
| Amaran Stok Rendah | `domain_event: inventory.threshold_reached` | notify_purchasing → draft_purchase_order |

## Nota Implementasi

- **Handler placeholder** — `internal_api`, `ai_task`, `send_notification` belum bersambung ke modul sebenar
- **`connector_api` dan `webhook_out`** — implementasi HTTP sebenar dengan timeout dan signature HMAC
- **`update_record` dan `create_task`** — berfungsi sebenar jika jadual wujud
- **Cron scheduler** — `processRetries()` perlu didaftarkan dalam `routes/console.php` atau `Kernel`
- **Approval flow** — langkah `waiting_approval` menghentikan run; perlu resume selepas kelulusan
