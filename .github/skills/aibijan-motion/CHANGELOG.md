# Changelog — AI BIJAN Motion 3D

## v1.5.0
Gaya baru, tanpa perubahan pada 10 gaya yang sudah ada.
- **Gaya baru #11 — PANELVERSE:** voice-over diceritakan sebagai komik pop retro yang hidup. Panel membanting ke halaman,
  terbelah, membesar jadi adegan 3D, pecah, dan membeku; titik halftone di bayangan, garis tinta tebal, warna pop,
  tulisan komik, cap stempel, garis kejut, banjir tinta, dan efek suara. Cukup satu kalimat ("Buatkan video PANELVERSE
  dari VO ini: …"): Claude menyusun beat per kalimat, memilih perangkat komik yang berbeda untuk tiap momen, memilih dan
  menyesuaikan tokoh serta tempat, merancang minimal satu benda khusus untuk naskahmu, mengecek gambarnya sendiri, lalu
  memberikan video final 1080×1920 @ 60 fps dengan efek suara (−14 LUFS).
- Isi mesin PANELVERSE:
  - tokoh parametrik (11 gaya rambut termasuk hijab, 7 jenis atasan, 5 bentuk tubuh, kacamata, kumis, dasi) dengan
    23 ekspresi;
  - 58 benda bergaya komik, 10 set siap remix, 8 panggung komik abstrak;
  - 12 tata letak panel, 12 transisi, 11 efek komik;
  - 45 efek suara sintesis yang ditempatkan otomatis dan diredam saat narasi bicara;
  - pemeriksa cerita (lint) yang menolak video monoton (tata letak, transisi, dan perangkat harus berganti) dan tulisan
    di luar area aman.
- Font komik baru (Bangers, Luckiest Guy, Comic Neue — SIL OFL 1.1) disertakan di `kits/panelverse/public/fonts`.
- `new-project.mjs`: folder cerita PANELVERSE (`src/pv-stories/`) di proyek yang sudah ada tidak pernah ditimpa.
- **Upload di aplikasi Claude kini bisa (cara A).** Upload skill di aplikasi menerima maksimal 200 file, sedangkan v1.4.x
  berisi 444 file sehingga ditolak ("Zip contains too many files"). Kode starter dan tiap kit kini dikemas di file
  `PACKED_SOURCE*.txt` dan dibuka otomatis oleh `new-project.mjs`, jadi skill ini hanya berisi 150 file. Dokumen, contoh,
  dan font tetap berupa file biasa. Proyek yang dihasilkan identik byte per byte dengan sebelumnya untuk ke-11 gaya.

## v1.4.1
Patch perbaikan sebelum distribusi. Tidak ada fitur baru; 10 gaya dan semua peningkatan v1.4.0 tetap.
- **Perbaikan setup:** `new-project.mjs --kit=precision` dan `--kit=innerworks` kini menyiapkan proyek dari starter
  (mesin inti). Sebelumnya ditolak karena kedua gaya itu memang tidak punya folder kit. Gaya lain tidak berubah.
- **Kebutuhan Node.js disamakan** di semua dokumen: minimal 22.18 untuk CINÉSTORY 3D, 22.13 untuk gaya lain
  (versi 22.18 ke atas cukup untuk semua gaya).
- **Acuan kualitas CINÉSTORY 3D** (`references/quality-bar/cinestory.jpg`): tujuh frame dari render uji CINÉSTORY yang
  lolos 13 cek otomatis dan review visual.
- **Batasan CINÉSTORY 3D ditulis lebih lengkap dan konsisten:**
  - 1–2 tokoh per adegan;
  - bangunan dari luar, benda rumit, dan sosok/makhluk dibuat sebagai bentuk sederhana;
  - kendaraan dan hewan bergerak tidak ditampilkan;
  - adegan berkelahi, olahraga, menari, berenang, memanjat, berpelukan, makan/minum, dan jatuh belum bisa;
  - VO berbahasa Indonesia;
  - versi 16:9 memakai subtitle file `.srt`;
  - baru diuji di Mac Apple Silicon.

  Contoh di dokumen yang bisa terbaca sebagai hewan bergerak atau badai dengan hujan sudah diperbaiki.
- **CHARACTER FLOW:** ikon di gelembung pikiran (?, !, ×) tidak lagi terpotong tepi atas frame. Ukuran dan posisinya
  menyesuaikan shot. Di close-up yang tidak menyisakan ruang di atas kepala, ikon tidak dimunculkan, dan ekspresi
  wajah yang membawa momennya. Gambar lain tidak berubah. Temuan ini muncul dari uji ulang sebelum rilis.

## v1.4.0
- **Gaya baru #09 — CINÉSTORY 3D:** film pendek 3D sinematik dari voice-over. Cukup satu kalimat ("Buatkan video
  CINÉSTORY 3D dari VO ini: …"): Claude membaca VO dan naskah, membangun lokasi dan benda yang dibutuhkan cerita (kantor,
  toko, jalan kota, rumah, hutan, pesisir…), menyutradarai tokoh, kamera, cahaya, teks, musik, dan efek suara otomatis,
  mengecek hasilnya (cek geometri, 13 cek otomatis, review visual), lalu memberikan video final **vertikal 9:16
  1080×1920 60 FPS** dengan subtitle di area aman TikTok/Reels + file `.srt`. Versi YouTube 16:9 kalau diminta. Brand kit
  opsional untuk penutup, watermark, dan intro.
- Proyek CINÉSTORY berdiri sendiri (`new-project.mjs --kit=cinestory`), sehingga gaya lain tidak berubah.
- CINÉSTORY menjaga variasi shot dan wajah: close-up tidak beruntun, insert ponsel dibuka dengan layar terlihat, gestur
  berpikir tanpa tangan menutupi wajah di close-up, kamera menghindari tubuh orang lain, dan cek baru menghentikan
  close-up yang hanya memperlihatkan belakang kepala.
- Dunia bawaan `kos`: pagar, tiang gerbang, tiang teras, motor, dan rumah tetangga kini dikenali kamera (tidak ada lagi
  shot dari balik tembok halaman). Kartu judul tidak menimpa wajah close-up. Callout bisa ditempel ke tokoh.
- **CINÉSTORY siap pakai berulang:** 5 world jadi yang sudah dites — `rumah` (ruang tamu, dapur, kamar, lorong belakang dengan lemari tua, teras),
  `sekolah` (kelas, koridor, lapangan), `kantor`, `minimarket` (+ jalan), `perpustakaan` — dan 38 objek siap pakai
  (perabot rumah, sekolah, kantor, toko). Film berikutnya tinggal memakai lokasi yang ada; `node scripts/worlds.mjs`
  menampilkan isi tiap world. Analyzer kini memilih world yang paling cocok, menuliskan semua pilihan di
  REQUIREMENTS.md, dan mengenali 47 kata benda (lemari, sofa, kulkas, kasur, TV, papan tulis, printer, CCTV…) sebagai
  objek library yang siap dipakai.
- **CHARACTER FLOW diperbarui besar:**
  - 6 lokasi: kampus, kamar, ruang kelas, kantor, kafe, ruang tamu;
  - satu video boleh pindah-pindah lokasi (`scenes`), lengkap dengan suasana suara per tempat;
  - 10 tokoh, termasuk dua berhijab, ibu, ayah, dan anak sekolah;
  - warna baju, rambut, hijab, dan rok bisa diganti per video;
  - sampai 4 tokoh per cerita (maksimal 2 per lokasi);
  - QA otomatis kini 13 cek, ditambah lembar review visual.
- CHARACTER FLOW lebih rapi di layar:
  - HP di tangan tetap masuk frame;
  - pose tangan (berpikir, bicara) turun sendiri dan tidak menempel sampai beat berikutnya;
  - gelembung pikiran tidak terpotong close-up dan membesar di shot lebar;
  - lampu kamar malam tidak lagi membuat titik silau di dinding, dan seprai siang tidak pecah putih;
  - tidak ada lagi shot "over-the-shoulder" ke diri sendiri;
  - shot berdua di ruangan memilih sudut yang memuat keduanya;
  - tidak ada cut yang terlalu cepat (< 0,7 dtk);
  - teks caption tidak menimpa wajah maupun gelembung pikiran;
  - ruangan siang dan baju putih tidak pecah putih.
- CINÉSTORY: insert HP mencari sudut di atas bahu yang tidak terhalang kepala/leher, termasuk pindah ke bahu lain
  (sebelumnya bisa menampilkan tengkuk). Kamera tidak lagi didorong jauh dari pintu yang tertutup.
- CHARACTER FLOW: urutan adegan dicek. Cue yang jatuh sebelum adegan sebelumnya (kata yang diucapkan berulang)
  dihentikan dengan saran `kata#n`.
- Proyek berisi banyak video: satu cerita yang sedang salah tidak lagi menggagalkan render cerita lain (CINÉSTORY dan
  CHARACTER FLOW).
- Paket npm: `zod` ditambal (GHSA-mrhx-c7xc-m3w5) — `npm ci` kini 0 kerentanan.
- Node.js minimum kini 22.18 untuk CINÉSTORY (gaya lain tetap 22.13).

## v1.3.0
- **Gaya baru #10 — CHARACTER FLOW:** tokoh 3D ekspresif memerankan cerita dari voice-over. Cukup satu kalimat
  ("Buatkan video CHARACTER FLOW dari VO ini: …"): Claude membaca VO, menyusun adegan, memilih tokoh, aksi, ekspresi,
  dan kamera otomatis, membuat musik + efek suara, mengecek hasilnya, lalu memberikan video final 1080×1920 60 FPS.
  Versi ini: lokasi kampus (siang/sore) dan kamar tidur (malam/siang), 5 tokoh (maks 2 per video), 38 aksi,
  26 ekspresi, HP dan tas selempang, gelembung pikiran, papan dan tumbleweed lucu. Tanpa lip-sync.
- Transkripsi VO otomatis dengan whisper.cpp (opsional), atau dari teks naskah. Narasi TTS offline (macOS) hanya
  dengan persetujuanmu.
- `new-project.mjs`: Claude menyiapkan proyek + mesin gaya dengan satu perintah.
- Pesan progres render lebih ringkas; `check-env` juga melaporkan whisper.cpp dan suara TTS bila tersedia.
- Nomor gaya #09 dicadangkan untuk gaya yang masih dikembangkan.

## v1.2.0
- **Empat gaya kini punya mesin khusus:** PIXELSTORY (pixel art 2.5D dengan cahaya per piksel), VOXELVERSE (dunia miniatur voxel), PAPERWORLD (diorama kertas, termasuk pintu tembus ke dunia lain), dan BUILDFORGE (bangunan dirakit tahap demi tahap, dengan pengecekan urutan konstruksi otomatis). Masing-masing disertai contoh teknis, jadi tampilannya konsisten dari film ke film.
- Semua gaya sekarang bisa dipilih tanpa label "eksperimental"; PRECISION dan INNERWORKS tetap dibangun di atas mesin inti.
- Empat font pixel dan judul berlisensi OFL (Anton, Instrument Serif, Silkscreen, Space Grotesk) ikut di paket PIXELSTORY.
- Pengaman tambahan di efek kamera: satu piksel bermasalah tidak lagi bisa membuat seluruh frame tampak putih atau pudar.

## v1.1.0
- **Brand kit:** `aibijan-brand.json` (nama channel, handle, CTA, warna) dipakai di teks judul, penutup, dan label. Warna aksen hanya dipakai kalau tetap terbaca di atas latar gayanya. File yang sama berlaku untuk semua skill AI BIJAN.

## v1.0.1
- **Font ikut di dalam paket.** Manrope sekarang disertakan, jadi render tidak lagi bergantung pada koneksi ke Google Fonts dan hasil teks sama di semua komputer.
- **Volume final lebih konsisten.** Mastering mengukur ulang hasilnya dan mengoreksi otomatis sampai sekitar −14 LUFS, standar media sosial.
- **Komputer tanpa kartu grafis (GPU).** Kalau render 3D lewat GPU tidak bisa dipakai, render otomatis pindah ke mode software, tanpa perlu setting manual. Pilihan ini diingat untuk render berikutnya.
- **Panduan instalasi** diperbarui ke menu Claude terbaru (Customize → Skills) dan sinkronisasi ke Claude Code.
- **Status tiap gaya** sekarang dijelaskan di `START_HERE.md`.
- Perbaikan format `SKILL.md` agar terbaca di semua versi Claude Code.

## v1.0
- Rilis pertama: 8 gaya sinematik (PIXELSTORY, VOXELVERSE, PRECISION, PAPERWORLD, INNERWORKS, WORLDFORGE, TIMEFORGE, BUILDFORGE), starter Remotion + React Three Fiber, mesin TIMEFORGE dan WORLDFORGE, preview 540×960 dan final 1080×1920 60 FPS dengan voice-over.
