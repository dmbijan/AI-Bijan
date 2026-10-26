# B-Boardroom Workflow (BAB 20)

Aliran kelulusan dan keputusan berasaskan 7 langkah. Pemilik mengemukakan persoalan, ejen AI berkaitan memberi dapatan, dan pemilik membuat keputusan yang direkodkan.

## Aliran 7 Langkah

| # | Langkah | Pelaku |
|---|---------|--------|
| 1 | Pemilik mengemukakan persoalan atau keputusan | `owner` |
| 2 | Chief of Staff menentukan ejen relevan | `chief_of_staff` |
| 3 | Ejen menerima konteks minimum | `system` |
| 4 | Setiap ejen kemukakan dapatan, andaian, bukti & risiko | `agents` |
| 5 | Orchestrator mengesan percanggahan & minta semakan | `orchestrator` |
| 6 | AI CEO sediakan ringkasan keputusan & pilihan | `ai_ceo` |
| 7 | Pemilik pilih tindakan; sistem rekod keputusan | `owner` |

> **Peraturan penting:** Boardroom **TIDAK** boleh menganggap persetujuan ejen sebagai bukti kebenaran. Dapatan mesti membezakan fakta, anggaran dan andaian.

## Struktur Modul

```
app/Modules/Boardroom/
├── BoardroomServiceProvider.php
├── config/boardroom.php              # 7 langkah, agent selection, finding types
├── Models/
│   ├── BoardroomSession.php          # Sesi boardroom
│   └── BoardroomFinding.php          # Dapatan ejen
├── Services/
│   └── BoardroomOrchestrator.php     # Enjin 7 langkah
└── Http/Controllers/
    └── BoardroomController.php
```

## Jenis Dapatan

Setiap dapatan **mesti** mempunyai jenis yang jelas:

| Jenis | Label | Perlu Sumber |
|-------|-------|--------------|
| `fact` | Fakta | ✅ Ya |
| `estimate` | Anggaran | ✅ Ya |
| `assumption` | Andaian | ❌ Tidak (perlu pengesahan) |
| `forecast` | Ramalan | ✅ Ya |
| `recommendation` | Cadangan | ❌ Tidak |

Dapatan jenis `fact`, `estimate`, `forecast` **akan ditolak** jika tiada `source_reference`.

## Pemilihan Ejen

Dipetakan mengikut kategori persoalan:

| Kategori | Ejen |
|----------|------|
| `financial` | cfo, ceo, ciso |
| `marketing` | cmo, ads_specialist, analyst |
| `operations` | coo, chro, analyst |
| `sales` | ciso, cmo, analyst |
| `hr` | chro, cfo, coo |
| `strategy` | ceo, cfo, coo, cmo |
| `risk` | ceo, cfo, coo |
| `customer` | support, ciso, cmo |
| `research` | researcher, analyst, ceo |
| `default` | ceo, cfo, coo |

Kategori dikesan automatik dari teks persoalan (kata kunci BM/EN), atau boleh ditetapkan manual.

## Pengesanan Percanggahan

Orchestrator membandingkan skor keyakinan dapatan dengan tajuk sama:

- Jika `max - min > 0.3` → percanggahan dikesan
- Dapatan berkonflik ditanda `requires_review = true`
- Sesi masuk status `awaiting_review`
- `review_round` ditambah (maksimum 2 pusingan)

## Status Sesi

```
draft → in_progress → awaiting_review → awaiting_owner_decision
                                                ↓
                              decided → closed
                              (atau cancelled pada bila-bila masa)
```

## Pilihan Keputusan

| Pilihan | Kesan |
|---------|-------|
| `approve` | Lulus cadangan → status `decided` |
| `reject` | Tolak cadangan → status `decided` |
| `defer` | Tangguh → status `in_progress` |
| `request_more_info` | Minta maklumat → status `in_progress` |
| `modify` | Ubah suai & lulus → status `decided` |

## API Endpoints

Semua di bawah `/api/v1/boardroom`.

### View (`boardroom.view`)

| Method | Endpoint |
|--------|----------|
| GET | `/sessions` |
| GET | `/sessions/{id}` |
| GET | `/sessions/{id}/findings` |
| GET | `/stats` |
| GET | `/metadata` |

### Create (`boardroom.create`)

| Method | Endpoint |
|--------|----------|
| POST | `/sessions` — Langkah 1 |

### Participate (`boardroom.participate`)

| Method | Endpoint | Langkah |
|--------|----------|---------|
| POST | `/sessions/{id}/select-agents` | 2 |
| POST | `/sessions/{id}/distribute-context` | 3 |
| POST | `/sessions/{id}/findings` | 4 |
| POST | `/sessions/{id}/detect-conflicts` | 5 |
| POST | `/sessions/{id}/prepare-summary` | 6 |
| POST | `/sessions/{id}/cancel` | — |

### Decide (`boardroom.decide`)

| Method | Endpoint | Langkah |
|--------|----------|---------|
| POST | `/sessions/{id}/decide` | 7 |
| POST | `/sessions/{id}/close` | — |

## Contoh Aliran Penuh

### Langkah 1 — Buka sesi

```bash
curl -X POST /api/v1/boardroom/sessions \
  -H "Authorization: Bearer {token}" \
  -d '{
    "question": "Adakah kita patut tambah seorang staf jualan bulan ini?",
    "category": "financial"
  }'
```

### Langkah 2 — Pilih ejen

```bash
curl -X POST /api/v1/boardroom/sessions/{id}/select-agents \
  -d '{"agents":["cfo","chro","ceo"]}'
```

### Langkah 3 — Edar konteks

```bash
curl -X POST /api/v1/boardroom/sessions/{id}/distribute-context
```

### Langkah 4 — Rekod dapatan

```bash
curl -X POST /api/v1/boardroom/sessions/{id}/findings \
  -d '{
    "agent_key": "cfo",
    "finding_type": "fact",
    "title": "Margin kasar bulan lepas",
    "content": "Margin kasar 32%, melebihi sasaran 30%.",
    "source_reference": "finance:report:2026-09",
    "confidence_score": 0.95
  }'

curl -X POST /api/v1/boardroom/sessions/{id}/findings \
  -d '{
    "agent_key": "chro",
    "finding_type": "assumption",
    "title": "Kos staf baru",
    "content": "Anggaran RM3,500/bulan termasuk statutori.",
    "assumptions": ["Tidak termasuk overtime", "Berasaskan gred junior"],
    "confidence_score": 0.60
  }'
```

### Langkah 5 — Kesan percanggahan

```bash
curl -X POST /api/v1/boardroom/sessions/{id}/detect-conflicts
```

### Langkah 6 — Sediakan ringkasan

```bash
curl -X POST /api/v1/boardroom/sessions/{id}/prepare-summary
```

### Langkah 7 — Rekod keputusan

```bash
curl -X POST /api/v1/boardroom/sessions/{id}/decide \
  -d '{
    "decision": "approve",
    "reason": "Margin kukuh, mampu tampung kos staf tambahan."
  }'
```

## Keselamatan

- **Konteks disanitasi** — kunci sensitif (`password`, `token`, `api_key`, `secret`, `credential`, `ssn`, `credit_card`) dibuang sebelum diedarkan kepada ejen
- **Tenant isolation** — semua query di-skop kepada `tenant_id`
- **Keputusan pemilik sahaja** — hanya pengguna dengan `boardroom.decide` boleh rekod keputusan
- **Tiada keputusan dalam status salah** — `recordDecision()` menolak jika status bukan `awaiting_owner_decision`

## Nota Implementasi

- Dapatan dijana secara manual/API — tiada panggilan model AI sebenar (menunggu AI Gateway)
- `prepareSummary()` menyusun dapatan mengikut jenis tetapi tidak menjana teks AI
- Percanggahan dikesan berdasarkan divergensi skor keyakinan (bukan analisis semantik)
