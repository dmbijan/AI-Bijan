# Integrasi VOYNANN → AI BIJAN

Rebrand dan integrasi tiga skill video VOYNANN ke dalam AI BIJAN.

> Tarikh: 8 Nov 2026

---

## Ringkasan

| Sumber | Versi | Skill AI BIJAN | Kegunaan |
|--------|-------|----------------|----------|
| VOYNANN MOGRAPH | v1.7.0 | `aibijan-mograph` | Motion-graphics explainer 2D/2.5D daripada VO + skrip |
| VOYNANN Motion | v1.5.0 | `aibijan-motion` | Filem sinematik 3D/stylized 9:16 |
| VOYNANN Video Editor | v3.9.1 | `aibijan-video-editor` | Sunting rakaman sedia ada jadi MP4 60fps |

Semua skill berada dalam `.github/skills/` dan boleh dipanggil sebagai slash command
(`/aibijan-mograph`, `/aibijan-motion`, `/aibijan-video-editor`).

---

## Peta Rebrand

| Sebelum (VOYNANN) | Selepas (AI BIJAN) |
|---|---|
| `/voynann-mograph` | `/aibijan-mograph` |
| `/voynann-motion` | `/aibijan-motion` |
| `/voynann-video-editor` | `/aibijan-video-editor` |
| `voynann-brand.json` | `aibijan-brand.json` |
| `voynann-mograph-projects` | `aibijan-mograph-projects` |
| `voynann-mograph-starter` | `aibijan-mograph-starter` |
| `voynann-motion-project` | `aibijan-motion-project` |
| `voynann-editor-template` | `aibijan-editor-template` |
| `VOYNANN_BROWSER` | `AIBIJAN_BROWSER` |
| `VOYNANN_MODE` | `AIBIJAN_MODE` |
| `VOYNANN_MODELS_DIR` / `VY_MODELS_DIR` | `AIBIJAN_MODELS_DIR` |
| `~/.cache/voynann-models` | `~/.cache/aibijan-models` |
| `VOYNANN` | `AI BIJAN` |

**207 rujukan** ditukar · **0 rujukan VOYNANN tertinggal**.

---

## Struktur Skill

```
.github/skills/
├── aibijan-mograph/          # VOYNANN MOGRAPH v1.7.0
│   ├── SKILL.md              # Definisi skill + aliran kerja penuh
│   ├── BRAND_KIT.md          # Panduan brand kit
│   ├── START_HERE.md
│   ├── CHANGELOG.md
│   ├── scripts/              # preflight, find-inputs, clean-script
│   ├── references/           # STYLES, COMPONENTS, SOUND_DESIGN_SYSTEM
│   └── assets/starter/       # Projek Remotion (package.json, src/)
│
├── aibijan-motion/           # VOYNANN Motion v1.5.0
│   ├── SKILL.md
│   ├── kits/                 # 11 gaya: cinestory, characterflow, panelverse, ...
│   ├── starter/
│   └── references/
│
└── aibijan-video-editor/     # VOYNANN Video Editor v3.9.1
    ├── SKILL.md
    ├── assets/remotion/
    └── references/
```

---

## Modul Laravel: `VideoStudio`

Orkestrasi render melalui API.

### API

```
GET    /api/v1/video-studio/projects              Senarai projek
POST   /api/v1/video-studio/projects              Cipta projek (muat naik VO/skrip/video)
GET    /api/v1/video-studio/projects/{id}         Butiran + arahan render
PUT    /api/v1/video-studio/projects/{id}         Kemas kini
DELETE /api/v1/video-studio/projects/{id}         Buang
POST   /api/v1/video-studio/preflight             Semak Node/npm/FFmpeg
POST   /api/v1/video-studio/projects/{id}/render  Mula render
POST   /api/v1/video-studio/projects/{id}/progress Kemas kini kemajuan
GET    /api/v1/video-studio/projects/{id}/instructions  Arahan sesi agent
GET    /api/v1/video-studio/summary               Ringkasan penggunaan
GET    /api/v1/video-studio/brand-kits            Senarai brand kit
POST   /api/v1/video-studio/brand-kits            Cipta brand kit
GET    /api/v1/video-studio/brand-kits/{id}/json  Unduh aibijan-brand.json
```

### Skill & Gaya

| Skill | Gaya |
|---|---|
| `mograph` | MIDNIGHT · MINIATUR · ARSIP · BLUEPRINT · SKETSA · HIKAYAT · GEOMETRIK · TERMINAL |
| `motion` | CINÉSTORY · CHARACTERFLOW · PANELVERSE · INNERWORKS · PAPERWORLD · VOXELVERSE · TIMEFORGE · BUILDFORGE · WORLDFORGE · PIXELSTORY |
| `video-editor` | IMPACT · SYSTEM · EDITORIAL · CREATOR POP · MOGRAPH MIX · STORYTELLER · DEPTH · LUMEN · POSTER · NOTED |

### Brand Kit

Padanan `aibijan-brand.json`:

```json
{
  "version": 1,
  "brand": "Jenama Anda",
  "handle": "@jenamaanda",
  "cta": "Ikuti untuk tips lain",
  "colors": {
    "primary": "#1B2A4A",
    "accent": "#FF8A00",
    "background": "#FFF8EC"
  },
  "font": "Plus Jakarta Sans",
  "logo": "logo.png"
}
```

Font disokong: Plus Jakarta Sans · DM Sans · Space Grotesk · Instrument Serif

---

## Keperluan Render

| Keperluan | Guna |
|---|---|
| Node.js | Semua skill (Remotion) |
| npm | Pasang kebergantungan skill |
| FFmpeg | Render MP4 & pemprosesan audio |
| Chromium | Render (Motion 3D); boleh diganti `AIBIJAN_BROWSER` |

`POST /preflight` menyemak semua ini dan memulangkan laporan. Jika gagal,
projek ditanda `failed` dengan mesej jelas — **servis tidak pernah berpura-pura berjaya**.

---

## Aliran Kerja

```
1. Cipta projek video       POST /projects (muat naik VO + skrip)
2. Preflight                POST /preflight        → semak Node/npm/FFmpeg
3. Mula render              POST /projects/{id}/render
4. Arahan sesi agent        GET  /projects/{id}/instructions
5. Jalankan skill           /aibijan-mograph (dalam sesi Claude/agent)
6. Kemas kini kemajuan      POST /projects/{id}/progress
7. Output MP4               tersimpan dalam storage/public/video-studio/
```

---

## Nota

- **Sumber asal dikekalkan**: fail asal VOYNANN tidak diubah; rebrand dibuat pada salinan.
- **Aset binari** (font, audio, imej) disalin tanpa ubah suai.
- **Keselamatan**: setiap laluan dilindungi `sanctum` + `tenant` + `perm:video_studio.*`.
- **Entitlemen**: `video_studio` tersedia dalam plan Growth & Enterprise.
