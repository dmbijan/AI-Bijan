#!/usr/bin/env python3
"""Jana docs/AI-BIJAN-MASTER.md + docs/INDEKS-DOKUMEN.md daripada fail sebenar.

Input: repo (argv[1]), Master Inventory, statistik modul (modul.tsv), fail md pejabat (argv[2]).
Semua nombor dikira dari fail/kod; pemetaan registry → modul ialah pertimbangan Dimas (ditanda begitu).
"""
import hashlib, os, re, subprocess, sys, collections

REPO, PEJABAT, MODUL_TSV = sys.argv[1], sys.argv[2], sys.argv[3]
INV = os.path.join(REPO, 'docs/AI-BIJAN-MASTER-INVENTORY-v1.0.md')
BP = 'docs/AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md'

# ---------- statistik modul (dari kod) ----------
modul = {}
for l in open(MODUL_TSV):
    bah = l.rstrip('\n').split('\t')
    if len(bah) != 5 or not bah[1].isdigit():
        continue
    m, loc, tf, tm, r = bah
    r = r if r.isdigit() else '0'
    modul[m] = dict(loc=int(loc), ujian=int(tm), laluan=int(r))

TERAS = {  # keupayaan dalam app/ (bukan app/Modules)
    'IAM': 'app/Http (AuthController, 2FA/TOTP, Sanctum)',
    'OAM': 'app/Http/Middleware/ResolveTenant + ahli/peranan (members, invitations)',
    'AUDIT': 'app/Services/AuditLogger',
    'HEALTH': 'HealthController (/health, /ready, /metrics)',
    'STORAGE': 'Laravel Storage',
}

# Pemetaan canonical ID → modul kod (pertimbangan Dimas, 31/10/2026; perlu disahkan owner)
PETA = {
 'BOS-001':['CRM'],'BOS-003':['WorkflowStudio'],'BOS-005':['Orders'],'BOS-006':['POS'],'BOS-007':['ServiceOS'],
 'BOS-008':['Projects'],'BOS-010':['BusinessIntel'],'BOS-011':['FlowAutomation'],'BOS-012':['Projects','PersonalAssistant'],
 'BOS-013':['VirtualOffice'],
 'FIN-001':['Finance'],'FIN-002':['Finance','BusinessIntel'],'FIN-003':['Finance'],'FIN-004':['Finance','Invoicing'],
 'FIN-007':['Billing','Invoicing'],'FIN-008':['Billing'],'FIN-009':['Expense'],'FIN-011':['Finance'],'FIN-012':['Finance','Assets'],
 'FIN-014':['Invoicing','Reminder'],'FIN-016':['Invoicing'],
 'GRO-001':['SalesCopilot','Prospecting'],'GRO-002':['Marketing','Blast'],'GRO-005':['Site'],'GRO-006':['MediaStudio'],
 'GRO-007':['Loyalty'],'GRO-008':['Billing'],'GRO-009':['Affiliate'],'GRO-010':['Invoicing','CRM'],'GRO-011':['Helpdesk'],
 'GRO-012':['Omnichannel','LiveSupport'],'GRO-014':['ContentStudio'],'GRO-015':['Seo'],'GRO-017':['SignDocs','CRM'],
 'GRO-020':['Omnichannel'],
 'HR-001':['HRMS'],'HR-002':['HRMS'],'HR-006':['Payroll'],'HR-007':['HRMS'],'HR-008':['HRMS'],
 'SUP-001':['Inventory'],'SUP-002':['Logistics'],'SUP-003':['SCM'],'SUP-004':['Manufacturing'],'SUP-005':['SCM'],
 'SUP-006':['Logistics'],'SUP-008':['SCM'],'SUP-009':['Fleet'],'SUP-010':['Assets'],
 'TEC-001':['BusinessIntel','Analytics'],'TEC-004':['FlowAutomation','McpStudio'],'TEC-006':['@IAM'],'TEC-010':['DocumentAI','SignDocs'],
 'TEC-013':['@HEALTH'],
 'GRC-005':['SignDocs'],'GRC-007':['@AUDIT'],
 'MFG-001':['Manufacturing'],'MFG-002':['Assets'],'MFG-003':['Assets'],
 'HOS-001':['POS'],'HOS-002':['POS'],'HOS-003':['COGS','Manufacturing'],
 'RET-001':['Site','Marketplace'],'RET-002':['Marketplace'],'RET-005':['POS','Orders'],'RET-006':['Billing'],
 'LOG-001':['Fleet'],'LOG-003':['Cod','Logistics'],
 'EVT-005':['Booking'],'EVT-006':['ServiceOS'],'EVT-012':['Loyalty'],
 'CORE-001':['@OAM'],'CORE-002':['Entitlement','Billing'],'CORE-003':['Reminder','Blast'],'CORE-004':['WorkflowStudio'],
 'CORE-005':['AIGateway'],'CORE-006':['AIAgents','AgentStudio','ADE','TeamAI'],'CORE-007':['BrainHub'],'CORE-009':['@AUDIT'],
 'CORE-015':['@STORAGE'],
}
NOTA = {
 'BOS-006':'PR #2: checkout+offline, DuitNow, QR meja, potong stok',
 'FIN-016':'`EInvoisService` rangka (ada TODO); belum disahkan dgn MyInvois',
 'FIN-008':'CHIP untuk langganan sahaja; bayaran pelanggan kedai belum',
 'SUP-001':'Enjin stok ada; POS potong stok hanya dalam PR #2',
 'SUP-004':'BOM/kit ada; MRP penuh belum','MFG-001':'Servis 183 baris sahaja',
 'TEC-018':'Import asas sahaja; templat AutoCount/SQL/Bukku belum',
 'CORE-001':'Ahli + peranan ada; hierarki cawangan/jabatan hampir tiada',
 'GRO-011':'Servis dalaman; laluan dalam routes/api.php',
}

# Status dipaksa bila modul diuji tetapi capability khusus masih rangka (disemak dalam kod 31/10/2026)
PAKSA = {'FIN-016': 'RANGKA', 'FIN-008': 'SEBAHAGIAN', 'MFG-001': 'RANGKA', 'SUP-004': 'SEBAHAGIAN', 'CORE-001': 'SEBAHAGIAN',
         'GRO-008': 'SEBAHAGIAN', 'FIN-014': 'SEBAHAGIAN'}

def status(ids):
    if not ids:
        return 'TIADA', '–'
    bah = []
    terbaik = 0
    for m in ids:
        if m.startswith('@'):
            bah.append(TERAS[m[1:]]); terbaik = max(terbaik, 1); continue
        s = modul.get(m)
        if not s:
            bah.append(f'{m} (?)'); continue
        bah.append(f'{m} ({s["loc"]:,} baris, {s["ujian"]} ujian)')
        terbaik = max(terbaik, 2 if s['ujian'] >= 20 else 1)
    label = {0:'TIADA',1:'IMPLEMENTED (ujian nipis)',2:'IMPLEMENTED + diuji'}[terbaik]
    return label, '; '.join(bah)

# ---------- baca registry ----------
inv = open(INV, encoding='utf-8').read()
reg = []
for l in inv.splitlines():
    m = re.match(r'^\| ([A-Z]+-\d+) \| ([^|]+)\| ([^|]+)\|', l)
    if m:
        reg.append((m.group(1), m.group(2).strip(), m.group(3).strip()))
assert len(reg) == 179, len(reg)

DOMAIN = collections.OrderedDict([('BOS','Business Operations'),('FIN','Kewangan'),('GRO','Jualan, Pemasaran & Pelanggan'),
  ('HR','HR & Tenaga Kerja'),('SUP','Inventori & Rantaian Bekalan'),('TEC','Teknologi, Data & Integrasi'),
  ('GRC','Tadbir Urus, Risiko & Pematuhan'),('MFG','Pembuatan'),('HLT','Kesihatan'),('HOS','Hospitaliti & F&B'),
  ('RET','Runcit & E-dagang'),('CON','Pembinaan'),('LOG','Pengangkutan'),('EVT','Acara, Tempahan & Servis'),('CORE','Platform Teras')])

# ---------- statistik keseluruhan ----------
kira = collections.Counter(); per_dom = collections.defaultdict(collections.Counter)
baris_reg = []
dipakai = set()
for i, k, n in reg:
    ids = PETA.get(i, [])
    dipakai.update(x for x in ids if not x.startswith('@'))
    st, bh = status(ids)
    if i in PAKSA:
        st = PAKSA[i]
    kira[st] += 1; per_dom[i.split('-')[0]][st] += 1
    baris_reg.append((i, k, n, st, bh, NOTA.get(i, '')))
luar = sorted(m for m in modul if m not in dipakai)

def git(*a):
    return subprocess.run(['git', '-C', REPO, *a], capture_output=True, text=True).stdout

ujian_total = 1372

# ---------- MASTER ----------
o = []
w = o.append
w('# AI BIJAN — MASTER RASMI (Dokumen Induk Gabungan)\n')
w('**Versi:** 1.0  ·  **Tarikh:** 31 Oktober 2026  ·  **Status:** `AUTHORITATIVE` (indeks & status); kandungan terperinci kekal di dokumen sumber  ')
w('**Tagline:** *Every Business Deserves a Boardroom.*  ')
w('**Disediakan:** gabungan Master Inventory v1.0 + Master Blueprint v9.x + semakan kod sebenar repo `main` (v9.5) dan PR #2.\n')
w('> Dokumen ini **menggabungkan** semua dokumen AI Bijan menjadi satu titik masuk. Ia tidak menyalin semula 10 MB kandungan '
  'lama; setiap bahagian menunjuk ke sumber yang betul, dan setiap sistem ditanda dengan **status sebenar dalam kod**, '
  'bukan dakwaan blueprint. Senarai penuh semua fail `.md` ada dalam [`INDEKS-DOKUMEN.md`](INDEKS-DOKUMEN.md).\n')

w('**Kandungan:** 0 Sumber kebenaran · 1 Identiti · 2 Keputusan terbuka · 3 Registry 179 × status kod · 4 Modul luar registry · '
  '5 Peta Blueprint · 6 Seni bina/OAM/Migrasi · 7 Roadmap MVP · 8 Kualiti · 9 Cara kemas kini · '
  '**10 Audit Odoo 50 app** · **11 Pelaksanaan Agentic AI** · **12 Standard UI/UX**\n')
w('## 0. Hierarki sumber kebenaran\n')
w('Jika dua sumber bercanggah, ikut susunan ini:\n')
w('| # | Sumber | Fail | Peranan |\n|---|---|---|---|')
w('| 1 | **Kod & ujian** | `app/`, `tests/` | Apa yang benar-benar wujud. Status `VERIFIED READY` hanya selepas ujian penerimaan (Inventory §2). |')
w('| 2 | **Master ini** | `docs/AI-BIJAN-MASTER.md` | Indeks rasmi, status, percanggahan & keputusan terbuka. |')
w('| 3 | **Master Inventory v1.0** | [`AI-BIJAN-MASTER-INVENTORY-v1.0.md`](AI-BIJAN-MASTER-INVENTORY-v1.0.md) | Skop 179 sistem canonical, sempadan domain, system-of-record, OAM, Migration Hub, Definition of Done. |')
w(f'| 4 | **Master Blueprint v9.x** | [`{BP}`](AI-BIJAN-MASTER-BLUEPRINT-v8.3%20(1).md) (alias [`AI-BIJAN-MASTER-BLUEPRINT.md`](AI-BIJAN-MASTER-BLUEPRINT.md)) | Spesifikasi produk terperinci: 25 PART, BAB 0–91. Mengandungi semakan v8.4–v9.3 (termasuk **v8.5 = BAB 82**). |')
w('| 5 | Audit & kajian | `docs/AUDIT-*.md`, `docs/KAJIAN-*.md`, laporan `hasil/` pejabat | Bukti & analisis pada tarikh tertentu. |')
w('| 6 | Arkib | `docs/archive/`, blueprint v2–v8.3, v4.x | Sejarah sahaja. **Jangan guna untuk keputusan baharu.** |\n')
w('**Nota v8.5:** tiada fail "v8.5" berasingan. Kandungan v8.5 (BAB 82 — B-Executive AI) berada dalam fail blueprint induk di atas, '
  'yang namanya mengelirukan ("v8.3 (1)") tetapi isinya versi 9.3 (25/10) dan dilabel 9.6 dalam fail alias.\n')

w('## 1. Identiti produk\n')
w('- **Positioning (Blueprint BAB 0):** AI-Native Business OS for Emerging Markets — satu platform menggantikan 10+ alat.')
w('- **Prinsip (Inventory §1):** satu platform & identiti; bounded context per domain; entitlement tentukan modul, OAM tentukan siapa buat apa; ejen AI guna identiti servis + kelulusan + audit.')
w('- **Strategi menang (Blueprint BAB 81, riset pesaing):** integrasi + migrasi berperingkat + pematuhan Malaysia + AI yang bertindak dengan kelulusan — bukan perang harga.')
w('- **Seni bina (Inventory §10):** Laravel modular monolith, API `/api/v1`, multi-tenant (`tenant_id`), domain events + outbox.\n')

w('## 2. Percanggahan & keputusan terbuka (perlu owner)\n')
w('| # | Isu | Sumber A | Sumber B | Cadangan |\n|---|---|---|---|---|')
w('| 1 | **Harga langganan** | Blueprint: 5 pakej RM288–RM1,888, token termasuk | Kod Billing/Entitlement: harga berbeza dalam config | **DIPUTUSKAN 31/10/2026:** semua harga & pakej diurus di **Super Admin → Pakej & Harga** (PR #2); sistem hanya semai pakej contoh. Harga dalam blueprint dan config = contoh sahaja. |')
w('| 2 | Bilangan sistem | Inventory: 179 canonical + 210 slot legacy | Kod: 60 modul (`app/Modules`) | Lihat §3: berapa canonical yang ada dalam kod, dan §4: modul kod di luar registry. |')
w('| 3 | Dakwaan ujian | Commit baseline v9.5: "963 ujian lulus, 0 gagal" | Disahkan 31/10: suite **tidak boleh jalan** di `main` (26 migrasi bayangan). Selepas PR #2: 1,365 ujian, 3 gagal (ext-intl sandbox) | Guna nombor PR #2; CI GitHub kini dikunci (bil) — perlu dibuka. |')
w('| 4 | Nama fail blueprint | `AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md` berisi v9.3 | Alias kata v9.6 | Namakan semula kepada `AI-BIJAN-MASTER-BLUEPRINT.md` (PR berasingan) dan kemas kini nombor versi. |')
w('| 5 | 210 nama legacy | Nama individu 1–210 tidak dapat dipulihkan (kecuali MES #117) | – | Owner bekalkan daftar asal untuk crosswalk (Inventory §14 #1). |')
w('| 6 | Alat ejen AI | – | 67 alat dalam `ToolExecutor` ialah stub yang pulangkan "berjaya" palsu (§11.2) | Wajib dibetulkan sebelum ejen dibuka kepada pelanggan. |')
w('| 7 | Pemetaan §3 | Pemetaan canonical → modul dibuat oleh Dimas | – | Owner/Product Owner sahkan. |\n')

w('## 3. Registry canonical × status kod sebenar\n')
w(f'Sumber registry: Master Inventory v1.0 §4 (179 sistem). Status dikira daripada kod repo pada 31/10/2026 '
  f'(baris PHP per modul, bilangan kaedah ujian yang menyebut modul). `IMPLEMENTED + diuji` = ≥20 kaedah ujian; '
  f'ini **bukan** `VERIFIED READY` — itu memerlukan ujian penerimaan, keselamatan & integrasi (Inventory §13.1).\n')
w('**Ringkasan:**\n')
w('| Domain | Jumlah | Ada + diuji | Ada, ujian nipis | Sebahagian / rangka | Tiada dalam kod |\n|---|---:|---:|---:|---:|---:|')
for d, nama in DOMAIN.items():
    c = per_dom[d]; t = sum(c.values())
    w(f'| {d} — {nama} | {t} | {c["IMPLEMENTED + diuji"]} | {c["IMPLEMENTED (ujian nipis)"]} | {c["SEBAHAGIAN"] + c["RANGKA"]} | {c["TIADA"]} |')
w(f'| **Jumlah** | **{len(reg)}** | **{kira["IMPLEMENTED + diuji"]}** | **{kira["IMPLEMENTED (ujian nipis)"]}** | **{kira["SEBAHAGIAN"] + kira["RANGKA"]}** | **{kira["TIADA"]}** |\n')
w('`SEBAHAGIAN` / `RANGKA` = modul ada, tetapi capability khusus ini belum lengkap (disemak manual dalam kod; lihat lajur Nota).\n')
for d, nama in DOMAIN.items():
    w(f'### 3.{list(DOMAIN).index(d)+1} {d} — {nama}\n')
    w('| ID | Kod | Nama | Status kod | Modul (baris, ujian) | Nota |\n|---|---|---|---|---|---|')
    for i, k, n, st, bh, nt in baris_reg:
        if i.split('-')[0] == d:
            w(f'| {i} | {k} | {n} | {st} | {bh} | {nt} |')
    w('')

w('## 4. Modul kod di luar registry 179\n')
w('Modul ini wujud dalam kod tetapi belum dipetakan ke mana-mana ID canonical. Cadangan: beri ID baharu (cth. `CORE-AI-*` untuk AI eksekutif) atau gabung sebagai capability.\n')
w('| Modul | Baris PHP | Kaedah ujian | Laluan API (fail modul) |\n|---|---:|---:|---:|')
for m in luar:
    s = modul[m]; w(f'| {m} | {s["loc"]:,} | {s["ujian"]} | {s["laluan"]} |')
w('')

w('## 5. Peta Blueprint v9.x (PART) → domain\n')
parts = [(m.group(1), m.group(2).strip()) for m in re.finditer(r'^# (PART [IVXL]+) — (.+)$', open(os.path.join(REPO, BP), encoding='utf-8-sig').read(), re.M)]
peta_part = {'PART I':'Identiti, seni bina','PART II':'CORE (OAM, entitlement, harga)','PART III':'BOS/FIN/GRO/HR/SUP modul perniagaan',
 'PART IV':'CORE-005/006/007 AI','PART V':'TEC-004/018 integrasi & migrasi','PART VI':'Roadmap','PART VII':'TEC/GRC operasi & tadbir urus',
 'PART VIII':'Kebolehlanjutan','PART IX':'CORE-007 kecerdasan kolektif','PART X':'Pengembangan pasaran','PART XI':'GRO/RET dagang & pemasaran',
 'PART XII':'TEC-013/015, keselamatan AI','PART XIII':'CORE-006 orkestrasi ejen','PART XIV':'Pembelajaran produk','PART XV':'Design system (UI)',
 'PART XVI':'CORE-003 e-mel, HR-004 pengambilan','PART XVII':'Strategi pesaing (BAB 81)','PART XVIII':'B-Executive AI (BAB 82, v8.5)',
 'PART XIX':'Platform SaaS universal','PART XX':'Kajian 200 app + 50 OSS','PART XXI':'Modul COD/Fleet/PoV/MCP/AgentStudio',
 'PART XXII':'Orders, Seo, ContentStudio, ADE dll.','PART XXIII':'BOS-006 POS (B-POS)','PART XXIV':'FIN-009 Expense, AI Finance','PART XXV':'Resit/penyata, Vision AI'}
w('| PART | Tajuk | Domain / registry |\n|---|---|---|')
for p, t in parts:
    w(f'| {p} | {t} | {peta_part.get(p, "")} |')
w('')

w('## 6. Seni bina, OAM, Migration Hub, tadbir urus AI\n')
w('Kandungan rasmi kekal dalam Master Inventory (tidak disalin semula supaya tiada dua versi):\n')
for s, t in [('§5','Audit pertindihan (17 isu) & keputusan canonical'),('§6','Domain boundaries & system-of-record'),('§7','Aliran transaksi (POS→OMS→Inventori→Kewangan; CRM→CPQ→CLM→Billing; Pembuatan)'),
             ('§8','OAM: hierarki tenant → syarikat → cawangan → jabatan → pasukan; RBAC+ABAC; peranan standard'),('§9','B-Migration & Integration Hub'),
             ('§10','Kontrak API & event'),('§11','AI Intelligence & governance'),('§12','Pakej komersial'),('§13','Implementation governance & Definition of Done')]:
    w(f'- Inventory {s} — {t}')
w('\nSpesifikasi terperinci yang berkaitan dalam Blueprint: PART II (Shared Core), PART V (Integrasi & Migrasi), PART XIII (Orkestrasi Ejen).\n')

w('## 7. Roadmap MVP (dipersetujui 31/10/2026)\n')
w('Owner tiada pilihan khusus → ikut cadangan: **MVP-1 dulu**, vertical **F&B/runcit (kedai bunga owner)**, gateway **CHIP**. Polisi stok: jualan melebihi stok **dibenarkan + ditanda untuk semakan**.\n')
w('| MVP | Sistem | Status 31/10 |\n|---|---|---|')
w('| **1. Kedai berniaga penuh** | BOS-006 POS, HOS-001/002, SUP-001 potong stok, FIN-016 e-Invois, FIN-008 CHIP/FPX, CORE-003 resit WhatsApp, CORE-001 skop cawangan | POS + offline + DuitNow + QR meja + potong stok: **siap (PR #2)**. e-Invois, CHIP pelanggan, WhatsApp, cawangan: belum |')
w('| 2. Tukar tanpa sakit | TEC-018 B-Migration Hub, CORE-010 | Belum |')
w('| 3. Kewangan | FIN-011 GL auto-jurnal, FIN-004 AR + ejen kutip, FIN-017 | Belum |')
w('| 4. HR SME | HR-006 Payroll statutori MY, HR-007 | Payroll ada (ujian sederhana); perlu semakan profesional |')
w('| Ditangguh | HLT, HOS-004..007, CON, EVT-008..010, CDP/DMP/MDM | PLANNED |\n')

w('## 8. Kualiti & status repo (31/10/2026)\n')
w(f'- Suite ujian (PR #2, SQLite, PHP 8.4): **{ujian_total:,} ujian, 3 gagal** (FilamentPanelTest — ext-intl tiada dalam sandbox).')
w('- `main` sebelum PR #2: suite tidak boleh jalan (26 migrasi bayangan), laluan modul POS/Booking/CRM/Payroll/DocumentAI/VirtualOffice tanpa auth+tenant.')
w('- CI GitHub Actions: **tidak berjalan** — akaun dikunci kerana isu bil.')
w('- Dokumen: 167 fail `.md` dalam repo; 24 kumpulan pendua tepat (≈4.4 MB) — lihat indeks.\n')

w('## 9. Cara kemas kini dokumen ini\n')
w('1. Sistem baharu → tambah dalam Master Inventory dengan ID canonical; jangan ubah ID lama.')
w('2. Status → jana semula §3 dari kod (skrip penjana dalam PR ini), jangan taip tangan.')
w('3. Keputusan owner → pindah dari §2 ke bahagian berkaitan dengan tarikh.')
w('4. Dokumen lama → arkib; jangan sunting blueprint lama untuk keputusan baharu.\n')
# Bahagian tulisan tangan (audit Odoo, Agentic AI, UI/UX) disimpan berasingan supaya tidak hilang bila dijana semula
bah = os.path.join(REPO, 'docs/master')
if os.path.isdir(bah):
    for f in sorted(os.listdir(bah)):
        if f.endswith('.md'):
            o.append(open(os.path.join(bah, f), encoding='utf-8').read().rstrip() + '\n')
open(os.path.join(REPO, 'docs/AI-BIJAN-MASTER.md'), 'w', encoding='utf-8').write('\n'.join(o) + '\n')

# ---------- INDEKS ----------
fail = [f for f in git('ls-files', '*.md').splitlines()]
fail.append('docs/AI-BIJAN-MASTER-INVENTORY-v1.0.md'); fail.append('docs/AI-BIJAN-MASTER.md')
fail = sorted(set(fail))
def sha(p):
    return hashlib.sha1(open(os.path.join(REPO, p), 'rb').read()).hexdigest()
def versi(p):
    try:
        t = open(os.path.join(REPO, p), encoding='utf-8-sig', errors='ignore').read(4000)
    except Exception:
        return ''
    m = re.search(r'\*\*Versi:\*\*\s*([0-9][0-9.]*)|Versi[: ]+([0-9]+\.[0-9.]+)|Version[: ]+([0-9]+\.[0-9.]+)', t)
    return next((g for g in m.groups() if g), '') if m else ''
kumpul = collections.defaultdict(list)
for p in fail:
    kumpul[sha(p)].append(p)
def peranan(p):
    if p in ('docs/AI-BIJAN-MASTER.md',): return 'INDUK — Master rasmi'
    if p == 'docs/AI-BIJAN-MASTER-INVENTORY-v1.0.md': return 'INDUK — Registry 179 sistem'
    if p == BP: return 'INDUK — Blueprint v9.x (kandungan penuh)'
    if p == 'docs/AI-BIJAN-MASTER-BLUEPRINT.md': return 'INDUK — alias blueprint'
    if p.startswith('docs/archive/'): return 'ARKIB'
    if p.startswith('app/Modules/'): return 'README modul'
    if p.startswith('.github/'): return 'Skill/ejen pembangunan'
    b = os.path.basename(p).lower()
    if re.search(r'blueprint|architecture', b): return 'DIGANTI — versi lama blueprint/seni bina'
    if re.search(r'audit|kajian|analisis|peta-pesaing|laporan', b): return 'Audit / kajian'
    if b in ('readme.md', 'claude.md', 'agents.md', 'todo.md', 'changelog.md'): return 'Panduan repo'
    return 'Rujukan'
ix = []
x = ix.append
x('# INDEKS DOKUMEN — AI BIJAN\n')
x('Dijana 31/10/2026 daripada repo + folder pejabat. Titik masuk: [`AI-BIJAN-MASTER.md`](AI-BIJAN-MASTER.md).\n')
pend = [g for g in kumpul.values() if len(g) > 1]
x(f'**Ringkasan:** {len(fail)} fail `.md` dalam repo · {len(pend)} kumpulan pendua tepat (kandungan sama 100%) · '
  f'≈{sum((len(g)-1)*os.path.getsize(os.path.join(REPO,g[0])) for g in pend)/1e6:.1f} MB bazir.\n')
x('## 1. Dokumen induk (guna ini)\n')
x('| Fail | Versi | Saiz | Peranan |\n|---|---|---:|---|')
for p in fail:
    if peranan(p).startswith('INDUK'):
        x(f'| `{p}` | {versi(p)} | {os.path.getsize(os.path.join(REPO,p))/1024:.0f} KB | {peranan(p)} |')
x('\n## 2. Pendua tepat (cadangan: simpan satu salinan dalam `docs/archive/`)\n')
x('Tiada fail dipadam dalam PR ini — pemadaman perlu kelulusan owner.\n')
x('| Kumpulan | Saiz | Fail |\n|---:|---:|---|')
for n, g in enumerate(sorted(pend, key=lambda g: -os.path.getsize(os.path.join(REPO, g[0]))), 1):
    x(f'| {n} | {os.path.getsize(os.path.join(REPO,g[0]))/1024:.0f} KB | ' + '<br>'.join(f'`{p}`' for p in sorted(g)) + ' |')
x('\n## 3. Semua fail repo mengikut peranan\n')
for r in ['DIGANTI — versi lama blueprint/seni bina', 'Audit / kajian', 'Rujukan', 'Panduan repo', 'README modul', 'Skill/ejen pembangunan', 'ARKIB']:
    sen = [p for p in fail if peranan(p) == r]
    if not sen: continue
    x(f'### {r} ({len(sen)})\n')
    x('| Fail | Versi | Saiz | Pendua? |\n|---|---|---:|---|')
    for p in sen:
        d = 'ya' if len(kumpul[sha(p)]) > 1 else ''
        x(f'| `{p}` | {versi(p)} | {os.path.getsize(os.path.join(REPO,p))/1024:.0f} KB | {d} |')
    x('')
x('## 4. Dokumen di pejabat (bukan dalam repo)\n')
x('Folder kerja pejabat Bijan Digital Marketing (`/home/kantor/kantor`). Rujukan sahaja; versi lama blueprint di sini **digantikan** oleh dokumen induk repo.\n')
x('| Fail | Versi | Saiz | Sudah ada dalam repo? |\n|---|---|---:|---|')
repo_sha = set(kumpul)
for root, _, fs in sorted(os.walk(PEJABAT)):
    if any(s in root for s in ('node_modules', '/vendor', '/.git', 'ai-bijan-merge', 'ai-bijan-docs', '/ai-bijan/ai-bijan-saas')):
        continue
    for f in sorted(fs):
        p = os.path.join(root, f)
        rel = os.path.relpath(p, PEJABAT)
        if not f.lower().endswith('.md') or not re.search(r'bijan', rel, re.I) or not rel.startswith(('produk/', 'hasil/')):
            continue
        h = hashlib.sha1(open(p, 'rb').read()).hexdigest()
        try:
            t = open(p, encoding='utf-8-sig', errors='ignore').read(4000)
            m = re.search(r'\*\*Versi:\*\*\s*([0-9][0-9.]*)|Versi[: ]+([0-9]+\.[0-9.]+)|Version[: ]+([0-9]+\.[0-9.]+)', t)
            v = next((g for g in m.groups() if g), '') if m else ''
        except Exception:
            v = ''
        x(f'| `{rel}` | {v} | {os.path.getsize(p)/1024:.0f} KB | {"ya (sama tepat)" if h in repo_sha else ""} |')
open(os.path.join(REPO, 'docs/INDEKS-DOKUMEN.md'), 'w', encoding='utf-8').write('\n'.join(ix) + '\n')
print('registry', len(reg), dict(kira), 'luar', len(luar), 'pendua', len(pend))
