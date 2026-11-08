# AI BIJAN Motion 3D — Mulai di sini

*Versi 1.4 · Early Access*

**AI BIJAN Motion** adalah *skill* untuk Claude yang membuat **video animasi 3D sinematik vertikal (9:16)**
dari ide, atau dari naskah + voice-over MP3. Kamu tidak perlu bisa coding. Kamu cukup bercerita;
Claude yang memilih gaya, menyiapkan proyek, membuat preview, lalu merender video final
**1080×1920, 60 FPS**.

## 3 langkah singkat

1. **Siapkan komputer** (sekali saja): Claude Code, Node.js, dan ffmpeg → lihat `INSTALLATION_GUIDE.md`.
2. **Pasang skill** ini → lihat `INSTALLATION_GUIDE.md`, bagian "Pasang skill".
3. **Ketik di Claude Code:**

   ```
   /aibijan-motion Aku mau bikin video evolusi kamera dari kamera film sampai mirrorless.
   ```

   Claude akan bertanya apakah kamu memakai voice-over, lalu mulai bekerja.

   **Baru — CINÉSTORY 3D (gaya #09):** punya voice-over yang bercerita — misteri, horor, kisah sehari-hari, "bagaimana kalau…"? Cukup satu kalimat:

   ```
   /aibijan-motion Buatkan video CINÉSTORY 3D dari VO ini: /Users/kamu/Downloads/vo.mp3 (naskahnya: /Users/kamu/Downloads/naskah.txt)
   ```

   Claude membangun lokasi ceritamu (kantor malam, toko, jalan kota, pesisir…), menyutradarai tokoh, kamera, cahaya, teks, musik, dan efek suara, lalu memberikan video final vertikal 9:16 (versi YouTube 16:9 kalau diminta) — tanpa storyboard, tanpa tanya-jawab.

   **CHARACTER FLOW (gaya #10):** punya voice-over tentang kehidupan sehari-hari? Cukup satu kalimat:

   ```
   /aibijan-motion Buatkan video CHARACTER FLOW dari VO ini: /Users/kamu/Downloads/vo.mp3
   ```

   Claude langsung menyutradarai tokoh 3D yang memerankan ceritamu dan memberikan video final — tanpa storyboard, tanpa tanya-jawab.

   **PANELVERSE (gaya #11):** mau ceritamu jadi komik pop retro yang hidup? Cukup satu kalimat:

   ```
   /aibijan-motion Buatkan video PANELVERSE dari VO ini: /Users/kamu/Downloads/vo.mp3
   ```

   Claude menyusun ceritamu jadi halaman komik yang bergerak — panel membanting dan membesar jadi adegan 3D, titik halftone, tulisan komik, dan efek suara — dengan tokoh, tempat, dan benda yang dirancang khusus untuk naskahmu, lalu memberikan video final.

## Panduan lain

| File | Isi |
|---|---|
| `INSTALLATION_GUIDE.md` | Kebutuhan komputer dan cara memasang skill |
| `USER_GUIDE.md` | Alur membuat video, review preview, revisi, dan video final |
| `EXAMPLE_PROMPTS.md` | Contoh ide untuk kesebelas gaya |
| `TROUBLESHOOTING.md` | Solusi masalah yang sering terjadi |
| `LICENSE_AND_REQUIREMENTS.md` | Lisensi pihak ketiga dan syarat penggunaan |
| `BRAND_KIT.md` | Nama channel, warna, dan logo kamu dalam satu file untuk semua skill AI BIJAN |
| `CHANGELOG.md` | Apa yang baru di tiap versi |

## Penting diketahui sebelum mulai

- Video dibuat **di komputermu sendiri** lewat **Claude Code** (tab *Code* di aplikasi desktop Claude, atau terminal). Chat biasa di claude.ai **tidak bisa** merender video ini.
- Paling disarankan **Mac dengan chip Apple (M1 ke atas)**, atau PC dengan kartu grafis (GPU). Tanpa GPU, render 3D jauh lebih lambat. Sebelum mulai, Claude otomatis mengecek komputermu dan merender tes kecil.
- Satu video bisa memakan puluhan menit kerja Claude. Di paket **Pro**, batas pemakaian bisa tercapai di tengah jalan; paket **Max** lebih lega untuk video panjang.
- Proyek pertama perlu internet dan ±1,2 GB ruang disk.
- Hasil untuk setiap ide baru biasanya perlu 1–2 putaran perbaikan preview sebelum final. CINÉSTORY 3D, CHARACTER FLOW, dan PANELVERSE langsung memberikan video final; revisi bisa diminta setelahnya.

## Status gaya

| Status | Gaya | Artinya |
|---|---|---|
| **Sutradara otomatis** | CINÉSTORY 3D (#09) | Cukup kirim voice-over (+ naskah): Claude membangun lokasi dan benda yang dibutuhkan cerita, menyutradarai tokoh, kamera, cahaya, teks, musik, dan efek suara otomatis. Vertikal 9:16 (16:9 kalau diminta). Tokoh bergaya stylized, tanpa lip-sync, 1–2 tokoh per adegan; bangunan luar, benda rumit, dan sosok/makhluk berbentuk sederhana; kebakaran besar, ledakan, hujan, keramaian, hewan bergerak, dan kendaraan ditampilkan lewat suasana, suara, atau di luar layar. VO berbahasa Indonesia; diuji di Mac Apple Silicon |
| **Sutradara otomatis** | CHARACTER FLOW (#10) | Cukup kirim voice-over: Claude menulis alur adegan, tokoh berakting, kamera, teks, musik, dan efek suara otomatis. 6 lokasi (kampus, kamar, kelas, kantor, kafe, ruang tamu), boleh pindah lokasi dalam satu video, 10 tokoh termasuk berhijab, 1–2 tokoh per lokasi, tanpa lip-sync |
| **Sutradara per naskah** | PANELVERSE (#11) | Cukup kirim voice-over: Claude menyusun beat, memilih tokoh dan tempat, merancang benda khusus untuk naskahmu, lalu membuat komik 3D yang hidup — panel, halftone, tulisan komik, efek suara. Vertikal 9:16; tokoh stylized tanpa lip-sync; tanpa adegan berkelahi, menari, atau keramaian; tulisan di layar singkat (bukan subtitle penuh); diuji di Mac Apple Silicon |
| **Dengan mesin khusus** | PIXELSTORY, VOXELVERSE, PAPERWORLD, WORLDFORGE, TIMEFORGE, BUILDFORGE | Mesin gaya dan contoh teknisnya sudah disertakan; Claude tinggal menyusun cerita, tokoh, dan dunianya |
| **Dengan mesin inti** | PRECISION, INNERWORKS | Dibangun di atas mesin inti AI BIJAN (studio, cahaya, efek kamera); biasanya perlu satu putaran perbaikan preview lebih banyak |

Gambar contoh di folder `references/quality-bar/` adalah acuan kualitas, bukan jaminan hasil yang sama persis.
