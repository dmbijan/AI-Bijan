# ⚠️ ARKIB — JANGAN GUNA SEBAGAI RUJUKAN

Folder ini mengandungi dokumen yang **bukan** dokumentasi semasa AI BIJAN.

| | |
|---|---|
| **Blueprint kanonik** | [`../AI-BIJAN-MASTER-BLUEPRINT.md`](../AI-BIJAN-MASTER-BLUEPRINT.md) (**v9.6**) |
| **Baseline angka** | [`../AUDIT-BASELINE.md`](../AUDIT-BASELINE.md) |
| **Audit induk** | [`../AI-BIJAN-AUDIT-INDUK-LENGKAP.md`](../AI-BIJAN-AUDIT-INDUK-LENGKAP.md) |
| **Status arkib** | `ARCHIVED / NOT AUTHORITATIVE` — sejak 27 Okt 2026 |

> **Peraturan:** dokumen dalam folder ini **tidak boleh** menjadi sumber untuk
> keputusan reka bentuk, pelan pelepasan atau dakwaan pemasaran. Jika ada percanggahan
> dengan blueprint kanonik, **blueprint kanonik yang menang**.

---

## Kenapa ia di sini

Dokumen-dokumen ini ditemui dalam `docs/` semasa audit 26 Oktober 2026, tetapi
**tidak menerangkan sistem AI BIJAN** yang sebenar.

| Subfolder | Bilangan | Punca |
|---|---|---|
| `features/` | 28 fail | Dokumen **B-POS System** — disalin ke folder salah |
| `blueprint-lama/` | 28 fail | Versi blueprint v2.x–v8.x yang telah digantikan |
| `superpowers-bpos/` | 2 fail | Spesifikasi **B-POS System** (termasuk `whatsapp-web.js`) |
| (fail akar) | 7 fail | Dokumen **B-POS System** |

---

## ❌ `features/` + 7 fail akar — Dokumen B-POS System

Dokumen ini menerangkan **produk berasingan** (`b-pos-system`), bukan AI BIJAN.

**Bukti (disahkan terhadap kod AI BIJAN):**

| Petunjuk | Dalam dokumen | Dalam kod AI BIJAN |
|---|---|---|
| `DOKU_CLIENT_ID`, `senangPay` | Disebut | **0** dalam `.env.example` |
| `point_of_sales` (nama DB) | Disebut | **0** |
| `app/Services/Payments/` | Dirujuk | **Tidak wujud** |
| `DineOrder`, `KitchenService`, `ThermalPrintService` | Didokumenkan | **0 kelas** |
| `docs/features/` dirujuk | Ya | Direktori ini hanya wujud dalam B-POS |

**Contoh fail:** `configuration.md` (gateway pembayaran DOKU), `dine-in.md`,
`thermal-printer.md`, `cashier-shifts.md`, `multi-outlet.md`, `testing-manual.md`.

### ⚠️ `superpowers-bpos/` — AMARAN KESELAMATAN

`2026-06-23-whatsapp-webjs-design.md` mencadangkan **`whatsapp-web.js`**.

> ❌ **AI BIJAN MELARANG pendekatan ini.** `whatsapp-web.js` ialah automasi tidak
> rasmi yang **berisiko menyebabkan nombor WhatsApp diharamkan**. AI BIJAN hanya
> menggunakan **WhatsApp Business Cloud API** dengan templat diluluskan Meta,
> consent direkod, dan opt-out dihormati.
>
> Rujuk: `docs/SISTEM-AI-BIJAN.md` §3.3 (pagar) dan blueprint BAB 90.

> Rujukan B-POS yang sah: `C:\ai-bijan\AI-Bijan\b-pos-system\docs\`
> Audit integrasinya: [`../AI-BIJAN-MASTER-BLUEPRINT.md`](../AI-BIJAN-MASTER-BLUEPRINT.md) BAB 88

---

## ❌ `blueprint-lama/` — Blueprint Lapuk

Versi lama yang telah **digantikan sepenuhnya**:

- `architecture-v2.md` → `architecture-v2.5-*.md`
- `blueprint-v3.0.md`, `blueprint-v4.3-*.md`, `blueprint-v4.5.1-*.md`
- `AI-BIJAN-MASTER-BLUEPRINT.md`, `-v8.md`, `-v8.2.md`, `-v8.3.md`
- `ecosystem-v3.0*.md`, `industry-coverage-v4.0.md`
- `AUDIT-PELAN-INDUK.md`, `PELAN-TINDAKAN.md`, `AI-BIJAN-AUDIT-v8.md`

**Angka dalam fail ini lapuk** — cth angka modul 18/34/39/53/55 (semasa: **59**),
ujian 220/314/486/718 (semasa: **~1,119 kaedah ujian**).

✅ **Guna:** [`../AI-BIJAN-MASTER-BLUEPRINT.md`](../AI-BIJAN-MASTER-BLUEPRINT.md) (**v9.6**)
📊 **Baseline angka:** [`../AUDIT-BASELINE.md`](../AUDIT-BASELINE.md)

---

## ✅ Dokumen Semasa

| Dokumen | Isi |
|---|---|
| [`../SISTEM-AI-BIJAN.md`](../SISTEM-AI-BIJAN.md) | **Dokumen sistem lengkap** |
| [`../KATALOG-MODUL-AI-BIJAN.md`](../KATALOG-MODUL-AI-BIJAN.md) | Katalog 58 modul |
| [`../README.md`](../README.md) | Indeks dokumentasi |
| [`../AUDIT-DOKUMEN.md`](../AUDIT-DOKUMEN.md) | Laporan audit ini |

Sahkan angka bila-bila masa:

```bash
php skrip/semak-dokumen.php
```
