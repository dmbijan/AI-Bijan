# Changelog — AI BIJAN MOGRAPH

## v1.7.0
- **3 gaya baru, jadi total 8 gaya:**
  - **HIKAYAT:** legenda dan sejarah Nusantara dalam bentuk teater bayang. Ada kelir yang disinari lampu minyak, wayang berukir yang lubang tatahannya tembus cahaya, gunungan untuk pergantian adegan, tulisan di daun lontar, dan musik gamelan.
  - **GEOMETRIK:** uang, ekonomi, dan statistik dengan gaya Swiss/Bauhaus. Ada grid tegas, blok warna primer sebagai data, dan angka raksasa.
  - **TERMINAL:** penipuan online, data pribadi, dan AI di layar terminal hijau. Ada teks yang diketik, jendela log, alert yang glitch, dan potret pixel.
- **Suara per gaya:** gamelan sintetis untuk HIKAYAT, marimba + perkusi untuk GEOMETRIK, dan dark synth untuk TERMINAL, masing-masing dengan efek suara sendiri.
- Panduan gaya ditambah pelajaran dari demo: teks di daun lontar dikunci satu baris, cara memilih kata penanda waktu supaya tidak tertukar dengan kata yang sama di awal VO, dan cara menjaga true peak di gaya synth.
- 6 font baru berlisensi bebas: Cinzel, Cormorant Garamond, Archivo, Inter, VT323, JetBrains Mono.

## v1.6.0
- **5 gaya bernama.** Selain dua gaya yang sudah ada (MIDNIGHT dan MINIATUR), sekarang ada tiga gaya baru yang dipilih otomatis sesuai isi naskah:
  - **ARSIP:** gaya map berkas dokumenter untuk sejarah, asal-usul, mitos vs fakta, dan kasus. Ada koran, polaroid, stempel, kartu indeks, dan benang merah di papan gabus.
  - **BLUEPRINT:** gambar teknik untuk menjelaskan cara kerja mesin dan teknologi. Garisnya tergambar sendiri, ada tampilan urai isometrik, callout bernomor, garis ukur, dan panel DETAIL.
  - **SKETSA:** buku catatan hidup untuk psikologi dan kebiasaan. Ada doodle yang bergetar seperti animasi tangan, sketsa pensil di frame pertama, stabilo, dan page flip.
- **Pilih gaya sendiri:** cukup tambahkan "pakai gaya ARSIP" (atau BLUEPRINT, SKETSA, MIDNIGHT, MINIATUR) saat memanggil skill. Tanpa itu, Claude memilih sendiri dan menyebut gaya yang dipakai di laporan akhir.
- **Suara per gaya:** tiap gaya baru punya musik dan efek suara orisinal sendiri. ARSIP pakai noir dengan detak jam dan mesin ketik, BLUEPRINT pakai arpeggio tech dengan bunyi mekanik, SKETSA pakai lo-fi hangat dengan goresan pensil dan page flip. Semuanya dibuat di dalam proyek tanpa sampel pihak ketiga.
- **6 font baru berlisensi bebas:** Playfair Display, Courier Prime, Space Grotesk, IBM Plex Mono, Caveat Brush, Patrick Hand.
- Lembar kontak pratinjau (`sheet.jpg`) sekarang tetap jadi di FFmpeg yang tidak punya filter teks, cukup tanpa label waktu.

## v1.5.1
- **Cek akses jaringan di detik pertama.** Sebelum mulai, Claude mengecek apakah alat render (Remotion dari npm) bisa diunduh. Kalau akses jaringan chat tertutup, Claude langsung berhenti dan menjelaskan cara membukanya (Settings → Capabilities → Allow network egress, atau minta owner untuk akun Team/Enterprise), atau beralih ke Claude Code. Sebelumnya Claude baru gagal di `npm ci` dengan error 403, setelah beberapa menit, tanpa petunjuk.
- Panduan (START_HERE) menyebut syarat **network egress** dengan jelas, plus langkah perbaikan kalau muncul error 403 dari `registry.npmjs.org`.
- Claude Code di Windows: pengecekan `npm` di `check-env` tidak lagi salah melapor "npm belum terpasang" di Node.js versi baru.

## v1.5.0
- **Bisa langsung di chat Claude, tanpa install.** Upload skill, buka chat baru, lampirkan VO + naskah, ketik `/aibijan-mograph`. Claude merender MP4 60 FPS di dalam chat dan mengirimkan file-nya. Node.js, FFmpeg, dan browser sudah tersedia di chat, jadi tidak ada yang perlu dipasang. Claude Code tetap bisa dipakai, dan render di sana lebih cepat untuk video panjang.
- **Render bertahap yang bisa dilanjutkan.** Video dirender per potongan 2 detik. Kalau waktu satu langkah habis, Claude cukup menjalankan perintah yang sama lagi dan render lanjut dari titik terakhir. Video 40 detik di chat selesai dalam sekitar 5 menit.
- **Revisi lebih cepat:** perubahan kecil di satu bagian video hanya merender ulang bagian itu (sekitar 1,5 menit untuk perbaikan kecil), dan versi sebelumnya tetap tersimpan.
- **Semua tag ElevenLabs terhapus dari subtitle,** termasuk tag bebas seperti `[explaining]`, `[dramatic pause]`, atau `[berbisik]`. Sebelumnya hanya tag dari daftar tetap yang dihapus.
- **Efek suara dari satu daftar:** semua SFX di `audio-plan.json` otomatis dipasang di video, jadi tidak ada efek yang direncanakan tapi lupa dipasang.
- Pengecekan visual lebih hemat: cuplikan beberapa detik digabung dalam satu lembar kontak.

## v1.4.0
- **Subtitle sinkron per kata.** Tiap kata di naskah diselaraskan dengan suara di VO (biasanya meleset hanya 20–40 milidetik), kata yang sedang diucapkan disorot, dan subtitle tidak pernah memotong kalimat. Butuh Python 3; tanpa Python, video tetap jadi dengan timing perkiraan.
- **Library komponen siap pakai:** layar HP dan aplikasi bank, notifikasi, chat, angka rupiah yang berhitung, uang kertas bergaya, label harga, grafik batang dan garis, cincin persentase, termometer, peta Indonesia dengan pin kota dan rute, karakter dengan pose dan ekspresi (termasuk opsi berhijab), warung, serta panah dan lingkaran penunjuk. Adegan jadi lebih cepat dibuat dan hasilnya lebih konsisten.
- **Brand kit:** satu file `aibijan-brand.json` (nama channel, warna, logo) langsung mengubah warna dan identitas seluruh video, dengan cek keterbacaan otomatis. File yang sama dipakai di semua skill AI BIJAN.

## v1.3.1
- **Font baru: Plus Jakarta Sans** untuk judul dan teks, dengan cadangan otomatis untuk simbol seperti → supaya tampil sama di macOS, Windows, dan Linux. Jarak antar kata juga dirapikan.
- **Mastering audio lebih aman.** True peak sekarang konsisten di bawah −1,5 dBTP, jadi pengecekan audio otomatis langsung lolos tanpa revisi.
- Instruksi skill dirapikan supaya Claude tidak pernah mengubah file skill di komputermu.

## v1.3
- **Zero-prompt:** cukup lampirkan VO + naskah, lalu jalankan `/aibijan-mograph`.
- **Sound Department:** musik latar adaptif, SFX yang sinkron, ducking otomatis di bawah suara, dan pengecekan loudness/keheningan.
- Tag ElevenLabs (`[curious]`, `[whispers]`, dll.) otomatis dihapus dari teks di layar.
