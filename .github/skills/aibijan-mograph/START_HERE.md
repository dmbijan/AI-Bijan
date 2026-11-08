# AI BIJAN MOGRAPH v1.7.0 — Zero-Prompt + Sound Department + 8 Gaya

**Lampirkan VO + naskah → ketik `/aibijan-mograph` → dapat MP4.** Video motion graphic explainer 2D/2.5D, 1080×1920, 60 FPS, lengkap dengan musik, SFX, dan subtitle per kata. Skill ini terpisah dari skill 3D `aibijan-motion`.

Bisa dipakai di dua tempat:

| | **Chat Claude** (disarankan) | **Claude Code** |
|---|---|---|
| Perlu install | Tidak ada | Node.js, FFmpeg (Python opsional) |
| Syarat akun/jaringan | Code execution **dan** akses jaringan (network egress) aktif | Internet biasa |
| Cocok untuk | Video pendek 15–60 detik | Video panjang, render lebih cepat |
| Video 40 detik | ±5–10 menit sampai jadi | Lebih cepat, tergantung komputer |

## Cara A — Chat Claude (tanpa install)

1. Di Claude, buka **Settings → Capabilities** dan aktifkan **Code execution and file creation** (di tampilan terbaru namanya **Create and edit files**). Di halaman yang sama, nyalakan juga **Allow network egress**. Tanpa akses jaringan ini, Claude tidak bisa mengunduh alat render (Remotion) dan videonya tidak bisa dibuat.
2. Buka **Customize → Skills → + → Create skill → Upload a skill**, lalu pilih **file ZIP ini langsung** (tidak perlu diekstrak).
3. Pastikan `aibijan-mograph` dalam keadaan aktif.
4. Buka **chat baru**, lampirkan file VO (`.mp3`, `.wav`, atau `.m4a`) dan naskah (`.txt`, `.md`, atau tempel teksnya), lalu ketik:

```text
/aibijan-mograph
```

Claude menyiapkan alatnya, menyusun adegan, membuat musik dan SFX, merender, lalu mengirim MP4 yang bisa langsung kamu putar dan download. Render dikerjakan bertahap, jadi wajar kalau Claude menjalankan render beberapa kali sebelum videonya jadi.

- Revisi dilakukan di **chat yang sama**, cukup dengan bahasa biasa ("musiknya lebih tegang di tengah", "detik 12 tulisannya kebesaran"). Revisi kecil hanya merender ulang bagian yang berubah.
- Di chat baru, Claude menyiapkan alatnya lagi dari awal (sekitar 1–2 menit).
- Akun Team/Enterprise: code execution dan network egress diatur oleh owner organisasi di **Organization settings → Capabilities**. Minta owner mengizinkan network egress, minimal **package managers only** (npm, PyPI, dan GitHub sudah termasuk).
- Di awal setiap video baru, Claude mengecek akses jaringan dalam beberapa detik. Kalau tertutup, Claude langsung berhenti dan memberi tahu cara membukanya, jadi waktu dan kuota tidak habis untuk pekerjaan yang pasti gagal.

## Cara B — Claude Code (di laptop/PC)

1. Pasang skill seperti Cara A langkah 2–3, lalu buka Claude Code (tab **Code** di aplikasi desktop Claude, atau terminal) dengan akun Claude yang sama. Ketik `/skills` untuk memastikan `aibijan-mograph` ada. Sinkronisasi skill dari akun Claude butuh Claude Code v2.1.273 atau lebih baru dan login dengan akun Claude (bukan API key).
2. **Alternatif:** ekstrak ZIP lalu taruh seluruh folder `aibijan-mograph` di `~/.claude/skills/` (macOS/Linux) atau `C:\Users\<nama>\.claude\skills\` (Windows).
3. Pastikan Node.js 20+ dan FFmpeg terpasang. Claude mengecek otomatis dan memberi tahu kalau ada yang kurang. Python 3 opsional, untuk subtitle per kata yang lebih presisi.
4. Buka Claude Code di folder kerja mana pun (skill membuat subfolder proyek baru tanpa menimpa file lain), lampirkan VO + naskah, lalu ketik `/aibijan-mograph`. Hasil ada di `out/final/` di folder proyek.

## Pengaturan default

1080×1920, 9:16, 60 FPS, durasi mengikuti VO, motion graphics sinematik sesuai topik. Kalau tidak menyebut merek/channel, video tetap netral. Tag ElevenLabs seperti `[curious]`, `[whispers]`, atau `[dramatic pause]` dihapus dari subtitle tanpa mengubah file naskah aslimu. **Subtitle sinkron per kata**: tiap kata diselaraskan dengan suara di VO, dan kata yang sedang diucapkan disorot.

**8 gaya bernama:** Claude memilih gaya dari isi naskah, jadi kamu tidak perlu memilih:

| Gaya | Cocok untuk |
|---|---|
| **MIDNIGHT** | simulasi "kalau besok", uang, aplikasi, privasi (gelap, navy-kuning) |
| **MINIATUR** | kota, lingkungan, kebijakan untuk banyak orang (isometrik 2.5D) |
| **ARSIP** | sejarah, asal-usul, mitos vs fakta, kasus (map berkas, koran, polaroid, stempel, benang merah) |
| **BLUEPRINT** | cara kerja mesin dan teknologi (gambar teknik biru, tampilan urai, panel detail) |
| **SKETSA** | psikologi, kebiasaan, perasaan (buku catatan, doodle bergerak, stabilo) |
| **HIKAYAT** | legenda, cerita rakyat, sejarah Nusantara (teater bayang, wayang berukir, gunungan, daun lontar, gamelan) |
| **GEOMETRIK** | uang, ekonomi, statistik (grid Swiss, blok warna primer, angka raksasa) |
| **TERMINAL** | penipuan online, data pribadi, AI (layar terminal hijau, teks diketik, glitch) |

Mau gaya tertentu? Tambahkan satu kalimat, misalnya `/aibijan-mograph pakai gaya ARSIP` atau `pakai gaya HIKAYAT`. Tiap gaya punya musik dan efek suara sendiri yang dibuat orisinal.

**Brand kit:** lampirkan `aibijan-brand.json` (nama channel, warna, logo) bersama VO, dan videomu otomatis memakai identitas brand-mu. Panduan: `BRAND_KIT.md`.

**Sound Department otomatis:** dalam satu sesi, skill menjalankan peran Sound Director → Music Supervisor → SFX Designer → Mixing Engineer → Audio QA. Ini tahapan kerja internal, bukan lima AI berlangganan. Musik dibuat orisinal oleh generator bawaan dan menyesuaikan mood cerita; hening dramatis tetap bisa disengaja. Generator musik bawaan adalah fondasi sintetis sederhana; selera musik tetap perlu dinilai dengan telinga.

## Kalau ada masalah

- **Claude bilang `npm ci` gagal, `registry.npmjs.org` diblokir (kode 403), atau "akses internet sandbox tertutup":** akses jaringan (network egress) di akun Claude-mu mati. Ini pengaturan akun, bukan kerusakan skill, dan tidak bisa diakali dari dalam chat. Buka **Settings → Capabilities**, nyalakan **Allow network egress**, lalu buka **chat baru** dan lampirkan lagi VO + naskah. Akun Team/Enterprise: minta owner organisasi mengizinkannya (minimal *package managers only*). Kalau pengaturan ini tidak bisa diubah, pakai **Cara B (Claude Code)**.
- **Chat tidak membuat file / skill tidak terpakai:** pastikan code execution aktif dan skill dalam keadaan aktif, lalu buka chat baru dan ketik `/aibijan-mograph`.
- **Subtitle per kata tidak tersedia:** video tetap jadi dengan timing perkiraan. Ini bisa terjadi kalau akses jaringan ke GitHub dibatasi (model penyelaras ±350 MB diunduh dari sana).
- **Render berhenti di tengah:** minta Claude melanjutkan ("lanjut render"). Bagian yang sudah jadi tidak hilang.
- **Claude Code: Node.js, npm, atau FFmpeg belum terpasang:** Claude akan menjelaskan apa yang kurang. Instalasi sistem bisa meminta izinmu.
- **Musik/SFX kurang pas:** minta revisi dengan bahasa biasa. Skill membuat versi MP4 baru dan versi lama tetap aman.

## Batasan dan lisensi

- Zero-prompt berarti tidak perlu menulis prompt kreatif; tetap butuh VO asli dan, di chat, code execution serta akses jaringan (network egress) yang aktif.
- Satu video bisa memakan puluhan menit kerja Claude. Di paket **Pro**, batas pemakaian bisa tercapai di tengah jalan; paket **Max** lebih lega untuk video panjang.
- Paket tidak mencakup akun/kuota Claude, VO, akun ElevenLabs, atau komputer.
- Subtitle per kata diselaraskan otomatis; nama atau angka yang tidak biasa tetap sebaiknya dicek dengan telinga.
- Remotion punya ketentuan lisensi tersendiri: https://www.remotion.dev/docs/license/pricing dan https://www.remotion.dev/docs/license/faq.
- Font: Plus Jakarta Sans, Playfair Display, Courier Prime, Space Grotesk, IBM Plex Mono, Caveat Brush, Patrick Hand, Cinzel, Cormorant Garamond, Archivo, Inter, VT323, JetBrains Mono (semuanya SIL Open Font License) dan DejaVu; lisensinya disertakan di `assets/starter/public/fonts/`.
- Penyelaras kata: model Omnilingual-ASR CTC (Apache-2.0) yang dijalankan dengan onnxruntime (MIT) dan numpy (BSD). Peta Indonesia memakai data Natural Earth (domain publik).

## Lisensi AI BIJAN MOGRAPH

- Kamu boleh memakai skill ini untuk membuat video, baik pribadi maupun komersial. Video yang kamu hasilkan menjadi milikmu, dengan tetap mematuhi lisensi pihak ketiga di atas, terutama Remotion.
- Skill, kode, dan dokumen di dalam paket ini **tidak boleh dijual ulang, dibagikan ulang, atau diunggah publik** tanpa izin tertulis dari penjual.
- Paket disediakan apa adanya. Hasil video bergantung pada bahan, perangkat, dan iterasi, dan tidak dijamin sama persis dengan contoh.

**Sebelum upload hasil video:** putar MP4 dengan suara, lalu cek hook, sinkronisasi caption, fakta, volume, dan bagian akhir.
