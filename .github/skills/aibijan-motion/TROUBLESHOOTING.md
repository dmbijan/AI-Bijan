# Troubleshooting

| Masalah | Penyebab umum | Solusi |
|---|---|---|
| `/aibijan-motion` tidak muncul | Skill belum terpasang atau di folder yang salah | Pastikan ada `~/.claude/skills/aibijan-motion/SKILL.md` (cara B di `INSTALLATION_GUIDE.md`), lalu buka ulang Claude Code |
| Ada dua skill AI BIJAN | Versi lama masih terpasang | Hapus atau nonaktifkan versi lama |
| Upload skill ditolak: "Zip contains too many files (maximum 200)" | File ZIP versi lama (1.4.x memuat lebih dari 200 file) | Upload `AI BIJAN_Motion_v1.5.0.zip` (150 file). Isi gaya dikemas di file `PACKED_SOURCE*.txt` dan dibuka otomatis saat proyek dibuat. Jangan ekstrak lalu ZIP ulang dengan tambahan file lain |
| Claude bilang tidak bisa merender | Kamu memakai chat claude.ai biasa | Gunakan Claude Code (tab *Code* di aplikasi desktop, atau terminal) |
| "Node.js belum terpasang" atau versinya lama | Node belum diinstal, atau di bawah 22.18 (CINÉSTORY 3D) / 22.13 (gaya lain) | Instal Node LTS dari nodejs.org, lalu buka ulang Claude |
| CINÉSTORY: Claude minta teks naskah | whisper.cpp belum terpasang | Kirim teks naskah yang sama persis dengan VO, atau pasang whisper (`INSTALLATION_GUIDE.md` langkah 3b) |
| CINÉSTORY: teks/aksi tidak pas dengan suara | Naskah berbeda dari yang diucapkan di VO | Jangan ubah waktu manual; kirim naskah yang sama persis lalu minta Claude membuat ulang |
| CINÉSTORY: nama/angka di subtitle salah | Kata salah dengar dari VO | Minta Claude memperbaiki ejaan kata itu (waktunya tidak diubah) |
| CINÉSTORY: mau versi YouTube 16:9 | Default-nya vertikal 9:16 | Minta "versi landscape 16:9" — Claude menyutradarai ulang untuk frame lebar (bukan crop) |
| CINÉSTORY: teks tertutup tombol TikTok | — | Laporkan detiknya; semua teks seharusnya di area aman, Claude memindahkan atau memendekkannya |
| CINÉSTORY: nama channel/logo tidak muncul | Tidak ada `aibijan-brand.json` | Taruh brand kit di folder kerja (lihat `BRAND_KIT.md`); tanpa file itu video memang tanpa branding |
| CINÉSTORY: Claude menolak menambah gaya lain ke proyek | Proyek CINÉSTORY berdiri sendiri | Buat video gaya lain di folder baru |
| CHARACTER FLOW meminta teks naskah | whisper.cpp belum terpasang | Kirim teks naskahnya, atau pasang whisper (`INSTALLATION_GUIDE.md` langkah 3b) |
| CHARACTER FLOW: teks/aksi sedikit lebih cepat/lambat dari suara | Timing tanpa whisper (rata-rata ±0,3 detik, di beberapa kata sampai ±0,5 detik) | Pasang whisper.cpp lalu minta Claude membuat ulang |
| CHARACTER FLOW: minta lokasi/aksi yang tidak ada | Di luar fitur versi ini (6 lokasi: kampus, kamar, kelas, kantor, kafe, ruang tamu; maks 2 tokoh per lokasi; tanpa lip-sync) | Claude menawarkan lokasi terdekat; satu video boleh pindah-pindah di antara 6 lokasi itu |
| CHARACTER FLOW: mau tokoh berhijab / baju lain | — | Minta saja ("tokohnya cewek berhijab biru", "pakai seragam putih"); warna baju, rambut, hijab, dan rok bisa diganti per video |
| CINÉSTORY: mau lokasi rumah / sekolah / kantor / toko | Sudah ada world jadi | Claude memakai world `rumah`, `sekolah`, `kantor`, `minimarket`, atau `perpustakaan` lebih dulu, jadi lebih cepat dan konsisten |
| PANELVERSE meminta teks naskah | whisper.cpp belum terpasang | Kirim teks naskahnya (tanda baca seperti yang diucapkan: koma dan titik di tempat jeda), atau pasang whisper (`INSTALLATION_GUIDE.md` langkah 3b) |
| PANELVERSE: tulisan di layar terlalu banyak / ingin subtitle penuh | PANELVERSE memakai tulisan komik singkat (1–3 kata) dan kotak narasi | Minta kata yang mau ditonjolkan; subtitle penuh tidak termasuk gaya ini |
| PANELVERSE: minta adegan berkelahi, menari, keramaian, atau tokoh asli/terkenal | Di luar fitur versi ini | Claude menawarkan versi komik yang mendekati (mis. benturan diganti efek "BRAK!" dan panel pecah) |
| PANELVERSE: video terasa mirip video sebelumnya | — | Minta "ganti gaya panel dan tempatnya"; setiap video seharusnya punya susunan panel, tempat, dan benda khusus sendiri |
| Render final terhenti di tengah | Sesi/komputer terputus | Minta Claude menjalankan render final lagi; bagian yang sudah selesai dipakai ulang |
| "ffmpeg tidak ditemukan" | ffmpeg belum diinstal | macOS: `brew install ffmpeg`; Windows: `winget install ffmpeg` |
| Persiapan pertama sangat lama | Mengunduh paket (±370 MB) dan browser render (±170 MB); diulang untuk tiap proyek baru | Tunggu sampai selesai; pastikan internet stabil |
| Render sangat lambat atau muncul pesan "switched to software rendering" | Kartu grafis (GPU) tidak bisa dipakai untuk 3D, jadi render otomatis pindah ke mode software | Normal, video tetap jadi tapi jauh lebih lama. Komputer dengan GPU atau Mac chip Apple jauh lebih cepat |
| Gambar tes hitam atau polos | Ada efek/shader yang salah | Minta Claude mengecek dan memperbaikinya |
| Ada titik-titik hitam di beberapa frame | Efek visual buatan Claude menghasilkan nilai tidak valid | Minta Claude memperbaiki efek tersebut (sistem mencegah frame kosong total) |
| Render berhenti atau "disk penuh" | Ruang disk kurang; cache proyek membesar | Kosongkan ruang, minta Claude menghapus `node_modules/.cache` proyek (aman), lalu lanjutkan render. Bagian yang sudah selesai dipakai ulang |
| Teks gagal dimuat atau render macet di awal | File font proyek hilang | Minta Claude memastikan folder `public/fonts` dari starter ikut tersalin, lalu ulangi |
| Suara tidak pas dengan gambar | VO diganti setelah timing disusun | Kirim VO final; Claude menghitung ulang timing |
| Suara terlalu pelan atau keras | — | Final dinormalkan ke −14 LUFS. Minta Claude menyesuaikan jika perlu |
| Hasil kurang "premium" | Ide terlalu umum | Beri detail: benda/tokoh spesifik, satu momen kuat, suasana warna. Minta satu putaran perbaikan preview |

Masih gagal? Salin pesan error dari Claude dan kirimkan ke penjual.
