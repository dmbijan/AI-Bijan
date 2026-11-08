# Panduan penggunaan

## Alur membuat video

1. **Panggil skill dan ceritakan idemu**, dengan bahasa sehari-hari:
   ```
   /aibijan-motion Aku mau video yang memperlihatkan cara kerja kipas angin, bagian dalamnya kelihatan.
   ```
   Kamu tidak perlu menyebut nama gaya; Claude memilih yang paling cocok.
2. **Jawab pertanyaan singkat.** Claude bertanya satu hal setiap kali. Yang terpenting: **pakai voice-over atau cukup musik/efek suara?**
3. **Kirim voice-over (jika ada):** file **MP3 atau WAV**, misalnya dari ElevenLabs. Lampirkan file-nya, atau tulis lokasinya, misalnya `@/Users/kamu/Downloads/narasi.mp3`. Claude mengukur durasi dan jeda suaramu. Video disusun mengikuti suaramu; suara **tidak** dipercepat atau dipotong.
   Belum punya naskah? Minta Claude membantu menulisnya, rekam dulu, lalu kirim file-nya.
4. **Setujui konsep.** Claude merangkum ide, gaya, durasi, dan audio dalam beberapa baris.
5. **Izinkan persiapan proyek.** Ini hanya sekali per proyek (lihat `INSTALLATION_GUIDE.md`).
6. **Review preview.** Claude mengirim video **preview 540×960** (lebih cepat dibuat). Minta perubahan dengan bahasa biasa:
   - "Kameranya lebih pelan di bagian awal."
   - "Warnanya lebih terang."
   - "Teks di akhir lebih besar."
   - "Ganti gayanya jadi kertas."
7. **Video final.** Setelah kamu setuju, Claude merender **1080×1920, 60 FPS**, menyelaraskan suara (−14 LUFS, standar media sosial), mengecek sinkronisasi, lalu memberi tahu lokasi file MP4-nya.

## CINÉSTORY 3D: alur satu kalimat

Untuk film cerita 3D dari voice-over, langkah 2–6 di atas juga dilewati: ketik
`/aibijan-motion Buatkan video CINÉSTORY 3D dari VO ini: <file VO> (naskahnya: <file naskah>)`, lalu Claude menyiapkan
proyek, membaca VO dan naskah, membangun lokasi dan benda yang dibutuhkan, menyutradarai, mengecek gambarnya sendiri,
dan memberikan **video final** 1080×1920 60 FPS dengan subtitle di area aman TikTok/Reels + file `.srt` (sekitar 20–45
menit kerja Claude untuk VO ±1 menit di Mac Apple Silicon; render finalnya sendiri ±2 menit). Mau versi YouTube?
Tambahkan "versi landscape 16:9" (subtitle-nya berupa file `.srt`; minta kalau mau ditempel di video). Minta revisi dengan bahasa biasa setelahnya.

## CHARACTER FLOW: alur satu kalimat

Untuk video tokoh 3D dari voice-over, langkah 2–6 di atas dilewati: ketik
`/aibijan-motion Buatkan video CHARACTER FLOW dari VO ini: <file VO>`, lalu Claude menyiapkan proyek, membaca VO,
menyusun adegan, mengecek gambarnya sendiri, dan memberikan **video final** (sekitar 5–15 menit untuk VO 30–40 detik
di Mac Apple Silicon setelah proyek siap). Minta revisi dengan bahasa biasa setelahnya.

## PANELVERSE: alur satu kalimat

Untuk video komik pop dari voice-over, langkah 2–6 di atas juga dilewati: ketik
`/aibijan-motion Buatkan video PANELVERSE dari VO ini: <file VO>` (tambahkan file naskah kalau whisper.cpp belum
terpasang). Claude membaca VO, menyusun beat per kalimat, memilih perangkat komik yang berbeda untuk tiap momen
(panel membanting, layar terbelah, panel pecah, zoom ke gelembung pikiran, cap stempel, banjir tinta…), merancang
minimal satu benda khusus untuk ceritamu, mengecek gambarnya sendiri, lalu memberikan **video final** 1080×1920 60 FPS
dengan efek suara (sekitar 15–30 menit untuk VO 15–40 detik di Mac Apple Silicon). Minta revisi dengan bahasa biasa
setelahnya, misalnya "panel ketiga ganti jadi layar terbelah" atau "tokohnya pakai kacamata".

## Di mana hasilnya?

Di folder proyek yang kamu setujui, di dalam subfolder `out/`, misalnya `~/AI BIJAN/kipas-angin/out/final.mp4`.

## Perkiraan waktu (Mac Apple Silicon)

| Tahap | Waktu |
|---|---|
| Persiapan proyek pertama | 2–15 menit (tergantung internet) |
| Claude membangun adegan baru | puluhan menit sampai sekitar 1 jam, termasuk perbaikan |
| Render preview 30 detik | ±20 detik |
| Render final 30 detik (1080×1920, 60 FPS) | ±1 menit |
| Render final 110 detik | ±3–6 menit |

Komputer yang lebih lemah akan lebih lambat.

## Tips agar hasilnya bagus

- **Sebut satu momen kuat**, misalnya "lensa memanjang keluar", "kubah kaca terbuka", atau "fondasi dicor".
- **Kirim voice-over final**, bukan draf, agar timing tidak perlu diulang.
- **Mulai dari video pendek (15–40 detik; CINÉSTORY 3D mulai ±25 detik).**
- Untuk topik sejarah atau sains, minta Claude memeriksa faktanya. Desain masa depan selalu disebut sebagai konsep.
- Gunakan hanya musik, logo, atau gambar milikmu sendiri atau yang berlisensi.

## Yang tidak cocok untuk skill ini

CINÉSTORY 3D versi ini: tokoh bergaya stylized tanpa lip-sync, 1–2 tokoh per adegan; bangunan dari luar, benda rumit, dan sosok/makhluk (misalnya hantu) dibuat sebagai bentuk sederhana. Kebakaran besar, ledakan, hujan/salju, keramaian, hewan bergerak, dan kendaraan (mobil, motor, sepeda) tidak ditampilkan langsung (lewat suasana, suara, atau di luar layar). Adegan berkelahi, olahraga, menari, berenang, memanjat atau naik tangga, berpelukan, makan/minum, dan jatuh belum bisa. VO dan naskah berbahasa Indonesia. Baru diuji di Mac Apple Silicon.

CHARACTER FLOW versi ini: 6 lokasi (kampus, kamar tidur, ruang kelas, kantor, kafe, ruang tamu) dan satu video boleh pindah-pindah lokasi; 10 tokoh (termasuk berhijab, ibu, ayah, anak sekolah) yang warna bajunya bisa diganti; maksimal 2 tokoh per lokasi; tanpa lip-sync, tanpa adegan lari, kendaraan, hewan, atau keramaian.


PANELVERSE versi ini: tokoh komik 3D stylized (tangan bulat tanpa jari terpisah), tanpa lip-sync; tanpa adegan berkelahi, menari, olahraga, atau keramaian; tempat berupa ruangan, jalan/taman sederhana, atau panggung komik abstrak; tulisan di layar berupa kata singkat dan kotak narasi (bukan subtitle penuh); hanya format vertikal 9:16.

Video penjelasan 2D/infografik, grafik data, video teks saja, slideshow foto, editing video asli, atau talking-head. Claude akan memberi tahu bila idemu kurang cocok.
