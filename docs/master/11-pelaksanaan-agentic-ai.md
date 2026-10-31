## 11. Pelaksanaan Agentic AI

**Matlamat:** ejen AI yang **benar-benar menjalankan kerja perniagaan** (bukan sekadar chatbot), selamat, boleh diaudit, dan sentiasa minta kelulusan untuk perkara berisiko. Sejajar dengan Master Inventory §11 (AI governance) dan Blueprint BAB 82 (tahap autonomi L0–L4).

### 11.1 Apa yang sudah ada dalam kod (disemak 31/10/2026)

| Lapisan | Modul / fail | Status |
|---|---|---|
| Gateway model AI | `AIGateway`: `GatewayService`, `ModelCatalogService`, `WalletService` (dompet kredit), `PiiRedactor`, `PromptGuard` | Ada, diuji |
| Orkestrasi ejen | `AIAgents`: `AgentOrchestrator`, `AgentService`, `AgentMemoryManager`, `AgentGovernance`, `ToolExecutor` | Ada; **alat stub** (lihat 11.2) |
| Pembina ejen | `AgentStudio`: `AgentBlueprintService` | Ada, diuji |
| MCP | `McpStudio`: `McpServerService` (initialize, tools/list, tools/call), `McpToolRegistry` | Ada, diuji |
| Autonomi | `ExecutiveAI`: `AutonomyService`, `ExecutivePreference.autonomy_level` | Ada, ujian sederhana |
| Aliran kerja & kelulusan | `WorkflowStudio`: `WorkflowEngine`; `POS/DiscountApprovalService` | Ada |
| Pengetahuan | `BrainHub`: insight, ramalan, cadangan | Ada, tiada ujian |
| Ejen pembangunan | `ADE`: koordinator, sandbox, semakan | Ada, diuji |
| Kredit & harga | `token_packages` + Super Admin (Pakej & Harga) | Ada (PR #2) |

### 11.2 Jurang kritikal (jujur)

**Semua 67 alat ejen dalam `AIAgents/Services/ToolExecutor.php` ialah stub** yang memulangkan keputusan tetap tanpa membuat apa-apa (contohnya `email.send` → `['email_sent' => true]`). Semakan lanjut (31/10/2026): stub ini **tidak boleh dicapai** kerana `toolExists()` membaca config `ai_agents.tools` yang tidak wujud, jadi setiap panggilan gagal dengan "Tool does not exist". Kesannya:
- Sebelum Fasa 1.1, ejen **tiada satu pun alat yang berfungsi**; mereka hanya boleh berbual.
- Jika config itu ditambah tanpa membetulkan pengendali, ejen akan terus mengaku "berjaya" untuk tindakan yang tidak berlaku. Ini lebih bahaya daripada gagal.

**Status Fasa 1.1 (cawangan `feat/agentic-asas`):** 6 alat sebenar (`AIAgents/Tools`) dengan kebenaran pengguna, Peti Kelulusan untuk risiko tinggi, pengesahan hasil dan audit; pengendali stub kini melontar `not_implemented`.

**Tindakan wajib sebelum mana-mana ejen dibuka kepada pelanggan:**
1. Alat stub mesti **gagal secara jelas** (`not_implemented`), bukan pulangkan "berjaya".
2. Setiap alat sebenar memanggil **servis domain sedia ada** (contohnya `SaleService`, `InvoiceService`), bukan menulis DB terus (peraturan repo: "AI agents use tools/API with limited permissions — no direct DB writes").
3. Setiap alat ada **ujian**, termasuk ujian bahawa ejen tak boleh melepasi kebenaran pengguna.

### 11.3 Seni bina sasaran

```text
Pengguna (WhatsApp / app / suara)
        │
        ▼
 Gateway AI (pilih model, PromptGuard, PiiRedactor, potong kredit dompet)
        │
        ▼
 Orkestrator ejen ──► Skill (arahan + senarai alat, versi)  ◄── AgentStudio / Studio AI
        │
        ▼
 Pendaftar Alat (satu sumber: dijana daripada servis domain + OpenAPI)
   - setiap alat: nama, skema input, kebenaran OAM (module.resource.action),
     tahap risiko, perlu kelulusan?, idempotency key, kos anggaran
        │
        ├── risiko RENDAH (baca, draf) ──► laksana terus
        └── risiko TINGGI (wang, harga, hantar keluar, padam, data pelanggan)
                 ──► Peti Kelulusan (CORE-004) ──► owner lulus di app/WhatsApp ──► laksana
        │
        ▼
 Audit + pengesahan hasil (semak rekod benar-benar berubah) + log kos
        │
        ▼
 MCP server (McpStudio) — alat yang sama boleh dipakai Claude/ChatGPT luar, ikut kebenaran yang sama
```

**Peraturan teras:**
- **Satu pendaftar alat** untuk orkestrator dalaman dan MCP luaran, jadi tiada dua set kebenaran.
- Ejen ialah **identiti servis** dengan tenant, kebenaran, kuota dan audit sendiri (Inventory §8). Ejen tak pernah lebih berkuasa daripada pengguna yang memanggilnya.
- **Pagar owner dikuatkuasa dalam kod**, bukan dalam prompt: keluar duit/ubah harga, padam data, hantar ke luar, dan sentuh data pelanggan **sentiasa** melalui Peti Kelulusan.
- **Pengesahan hasil:** selepas alat jalan, sistem membaca semula rekod untuk sahkan perubahan berlaku, sebelum ejen boleh lapor "siap".
- **Kredit telus:** setiap tindakan ejen menunjukkan anggaran kredit sebelum dijalankan; dompet tak boleh negatif.

### 11.4 Tahap autonomi (L0–L4, Blueprint BAB 82)

| Tahap | Ejen boleh | Contoh |
|---|---|---|
| L0 Nasihat | Jawab & terangkan sahaja | "Kenapa jualan turun minggu ni?" |
| L1 Draf | Sediakan draf; manusia hantar | Draf mesej kutip hutang |
| L2 Laksana risiko rendah | Tindakan dalaman boleh undur | Cipta tugas, tag pelanggan, jadual peringatan |
| L3 Laksana dengan kelulusan | Tindakan berisiko selepas owner lulus | Hantar PO ke vendor, hantar 200 mesej WhatsApp |
| L4 Autonomi berpagar | Laksana dalam had yang owner tetapkan | Pesan semula stok bawah RM500 dari vendor yang diluluskan |

Tahap ditetapkan **per ejen per syarikat** (`ExecutivePreference.autonomy_level`); lalai = L1.

### 11.5 Ejen pertama (ikut MVP)

| Ejen | Kerja | Alat utama | Tahap lalai | Metrik kejayaan |
|---|---|---|---|---|
| **Ejen Penyediaan** | Sediakan akaun dalam < 30 min: jenis bisnes, produk (dari foto/menu/Excel), kaunter, cukai | Import produk, cipta kaunter, tetapan cukai | L2 | Masa ke jualan pertama |
| **Ejen Kaunter** | Laporan tutup kedai, kesan jualan pelik, cadang promosi | Ringkasan POS, semakan stok | L1 | Laporan harian dibaca |
| **Ejen Stok** | Ramal stok habis, draf PO, semak "Semakan Stok" | Ramalan, cipta draf PO | L3 | Stok habis berkurang |
| **Ejen Kutip Hutang** | Senarai invois lewat, draf mesej sopan, hantar selepas lulus | AR, WhatsApp | L3 | Hari kutipan (DSO) turun |
| **Ejen e-Invois** | Semak invois sebelum hantar MyInvois, betulkan ralat biasa | Validasi, hantar LHDN | L3 | Kadar penolakan LHDN |
| **Ejen CFO Harian** | Ringkasan tunai, untung kasar, amaran perbelanjaan | GL, laporan | L0 | Digunakan setiap hari |
| **Ejen Migrasi** | Baca fail AutoCount/SQL/Bukku, cadang pemetaan, jalankan selepas lulus | Import, pemetaan, rekonsiliasi | L3 | Rekod dipindah tanpa ralat |
| **Ejen Pelanggan (WhatsApp)** | Jawab soalan, ambil tempahan/pesanan, serah ke manusia | Katalog, tempahan, sebut harga | L2 | Kadar jawapan < 1 min |

### 11.6 Langkah pelaksanaan

1. **Pendaftar alat sebenar + Peti Kelulusan:** tukar stub kepada `not_implemented`, wayarkan 10 alat pertama ke servis domain (POS, stok, invois, pelanggan, WhatsApp draf), dengan ujian kebenaran & kelulusan.
2. **Ejen Penyediaan + Ejen Kaunter** (risiko rendah, nilai tinggi untuk MVP-1).
3. **Ejen Stok + Ejen Kutip Hutang** (L3, melalui Peti Kelulusan di WhatsApp).
4. **MCP awam** guna pendaftar alat yang sama (pelanggan sambung Claude/ChatGPT sendiri).
5. **Studio AI**: ubah borang/aliran dengan ayat BM, pratonton, kemudian lulus.
6. **Eval berterusan:** set soalan ujian per ejen (ketepatan, tiada tindakan tanpa kelulusan, tiada kebocoran tenant), dijalankan dalam CI.

**Definition of Done ejen** (tambahan kepada Inventory §13.1): alat sebenar + ujian; pagar kelulusan diuji; pengesahan hasil; audit; anggaran kredit; eval lulus; boleh dimatikan per syarikat (*kill switch*).
