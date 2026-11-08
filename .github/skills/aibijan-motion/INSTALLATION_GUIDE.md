# Panduan instalasi

## 1. Kebutuhan

| Yang dibutuhkan | Keterangan |
|---|---|
| **Akun Claude dengan akses Claude Code** | Paket berbayar Claude (Pro, Max, Team, atau Enterprise) atau akun API Anthropic. Satu video bisa memakan puluhan menit kerja Claude; di paket Pro batas pemakaian bisa tercapai di tengah jalan, jadi **Max lebih lega** untuk video panjang |
| **Claude Code** | Aplikasi desktop Claude (tab *Code*) atau Claude Code di terminal |
| **Node.js versi 22.18 atau lebih baru** | Untuk menjalankan proyek video. CINÉSTORY 3D butuh minimal 22.18; gaya lain bisa mulai 22.13, jadi 22.18 ke atas cukup untuk semua gaya (diuji dengan versi 24 LTS) |
| **ffmpeg** | Untuk analisis voice-over dan audio video final |
| **Ruang disk** | Minimal 5 GB kosong. Setiap proyek memakai sekitar 1,2 GB |
| **Internet** | Untuk mengunduh paket dan browser render (±170 MB) saat proyek pertama |
| **Komputer** | Paling disarankan Mac dengan chip Apple (M1 ke atas), atau PC dengan kartu grafis (GPU). Tanpa GPU, render 3D jauh lebih lambat. Sebelum mulai, Claude mengecek komputermu dan merender tes kecil |
| *Opsional untuk CINÉSTORY 3D, CHARACTER FLOW, dan PANELVERSE:* **whisper.cpp** | Membaca kata-kata VO otomatis dan tepat, jadi cukup kirim VO. Tanpa ini, sertakan teks naskah VO-mu |

## 2. Pasang Node.js (sekali saja)

1. Buka <https://nodejs.org>, unduh versi **LTS**.
2. Instal seperti aplikasi biasa (klik *Next* sampai selesai).
3. Tutup lalu buka lagi Claude.

## 3. Pasang ffmpeg (sekali saja)

- **macOS:** jika sudah punya Homebrew, buka Terminal lalu ketik `brew install ffmpeg`. Jika belum punya Homebrew, minta Claude Code membantu: *"Tolong bantu aku instal ffmpeg."*
- **Windows:** buka PowerShell lalu ketik `winget install ffmpeg`, lalu tutup dan buka lagi Claude.

Tidak yakin sudah terpasang? Nanti Claude mengeceknya otomatis sebelum mulai.

## 3b. (Disarankan untuk CINÉSTORY 3D, CHARACTER FLOW, dan PANELVERSE) Pasang whisper.cpp

macOS, di Terminal:
```
brew install whisper-cpp
mkdir -p ~/.cache/whisper.cpp
curl -L -o ~/.cache/whisper.cpp/ggml-large-v3-turbo-q5_0.bin https://huggingface.co/ggerganov/whisper.cpp/resolve/main/ggml-large-v3-turbo-q5_0.bin
```
Model ±550 MB, sekali saja. Tanpa whisper tetap bisa: sertakan teks naskah VO saat meminta video.

## 4. Pasang skill

ZIP ini berisi satu folder: **`aibijan-motion`** (dengan file `SKILL.md` di dalamnya).

### Cara A — Upload skill di aplikasi Claude

1. Buka Claude (aplikasi desktop atau claude.ai) → **Customize** → **Skills** → tombol **+** → **Create skill** → **Upload a skill**.
2. Pilih file `AI BIJAN_Motion_v1.5.0.zip` (tidak perlu diekstrak).
3. Pastikan skill **aibijan-motion** dalam keadaan aktif.
4. Buka Claude Code dengan akun Claude yang sama (tab **Code** di aplikasi desktop, atau terminal), lalu ketik `/aibijan-motion`.

> Skill dari akun Claude ikut termuat di Claude Code versi **2.1.273 atau lebih baru**, selama Claude Code login dengan akun yang sama (bukan API key). Jika `/aibijan-motion` tidak muncul, perbarui Claude Code atau pakai cara B.

### Cara B — Salin folder (Claude Code)

1. Ekstrak (unzip) `AI BIJAN_Motion_v1.5.0.zip`.
2. Salin folder **`aibijan-motion`** ke folder skill pribadi Claude Code:
   - **macOS:** `~/.claude/skills/`. Di Finder tekan **⌘⇧G**, ketik `~/.claude/skills`, lalu tempel foldernya. Jika folder `skills` belum ada, buat dulu.
   - **Windows:** `C:\Users\<nama-kamu>\.claude\skills\`
3. Hasil akhirnya harus berupa `~/.claude/skills/aibijan-motion/SKILL.md`.
4. Tutup lalu buka lagi Claude Code, kemudian ketik `/aibijan-motion`.

**Sudah punya versi lama** (misalnya versi pengembangan)? Hapus atau nonaktifkan dulu yang lama, supaya tidak ada dua skill dengan nama yang sama.

## 5. Proyek pertama

Saat kamu meminta video pertama, Claude akan:

1. meminta izin membuat folder proyek (misalnya `~/AI BIJAN/nama-video`);
2. memasang paket proyek (±370 MB) dan mengecek komputermu;
3. merender tes kecil. Pertama kali, ia mengunduh browser render (±170 MB).

Langkah ini bisa memakan 2–15 menit, tergantung kecepatan internet. Setelah itu, proyek yang sama tidak perlu mengunduh ulang.
