# AI BIJAN Video Editor Â· v3.10.0 â€” Claude Skill

Edit video kamu cukup lewat chat, hasilnya seperti dikerjakan editor konten profesional. Upload footage,
sebut gayanya, lalu Claude:

- memotong jeda, "eh / emm / anu", dan kalimat yang kamu ulang,
- bikin subtitle animasi yang sinkron per kata,
- zoom dan push-in yang mengikuti wajah, dengan ritme yang bikin orang tetap nonton,
- menambah motion graphic dan transisi ala After Effects (motion blur asli, easing halus, animasi teks),
- **Premium Pack** ala editor AE mahal: judul gradient yang menyala dengan kilau cahaya, koin/centang/bintang 3D
  mengkilap yang berputar, pill kaca dengan badge centang, badge ikon yang melingkari kepala, kartu foto miring yang
  terbang ke samping kepala, checklist di atas frame yang di-blur, dan latar warna ala poster yang terbuka dari kepalamu,
- efek Reels kekinian ala After Effects (**Depth Pack**): teks raksasa **di belakang badan kamu** (muncul dari mask,
  "slam" dengan gema outline, dinding teks yang terus bergeser, atau dua kata yang terbelah dari balik kepala),
  **kata-kata yang mengorbit kepalamu** (separuh cincinnya lewat di belakang kepala), **doodle tangan** yang tergambar
  di sekitar kepala (percikan "ternyata", spiral "susah berhenti", tanda tanya, lampu ide, mahkotaâ€¦), **dinding feed
  tanpa ujung** dari frame videomu sendiri di belakang badan, camera shake dan glitch di kata kunci, serta **cutout**
  (background diganti, badan kamu "keluar" dengan outline putih) - background remove-nya diproses lokal, tanpa API
  berbayar,
- **scene motion graphic full-screen yang selang-seling dengan wajahmu** (gaya MOGRAPH MIX, rasa Reels After Effects
  premium): HP 3D yang memutar videomu, chat, jendela prompt AI, checklist fitur, kartu kaca - jadi bukan muka terus,
- **storytelling sinematik** (gaya STORYTELLER): warna dan mood video berubah mengikuti emosi tiap kalimat (kaget,
  berbisik, serius, gelap), search bar dan ikon 3D melayang di atas kepala, kata besar 3D di belakang badan, kaca
  pembesar yang membaca kata-kata, subtitle yang mengetik sendiri,
- **merancang visual dari isi ceritamu, apa pun naskahnya**: Claude mencari "gambar" dari tiap kalimat (angka, harga,
  waktu, perbandingan, proses, orang, rahasia, pertumbuhan, pesananâ€¦) lalu merakit scene-nya dari **scene kit**:
  orang dengan berbagai pose, kerumunan, 48 benda (rumah, toko, uang, jam, grafik, timbangan, gembok, kotak, truk,
  chat, lampu ide, pialaâ€¦), label harga yang berayun, gelembung chat, search bar yang mengetik, angka yang menghitung,
  grafik batang, panah, langkah bernomor, dan videomu sendiri di dalam bingkai HP. Jadi elemennya mengikuti
  naskahmu, bukan template,
- **gaya simpel ala catatan (NOTED)**: label huruf mono yang mengetik sendiri di atas kepalamu (topik tiap bagian,
  atau daftar yang bertambah "A Â· B Â· C"), potongan selotip kertas yang ditempel satu per satu di atas kepala pas kamu
  menyebutnya, coretan spidol saat kamu bilang "duluâ€¦ tapi sekarang", kartu catatan bergaris yang ditempel di atas
  frame yang di-blur untuk daftar, kata kunci raksasa yang distabilo dengan catatan kecil + panah di sampingnya, dan
  klip/foto milikmu sendiri yang ditempel jadi kartu di sekitar kepala - subtitle-nya kalem dengan stabilo kuning,
- memasang musik dan sound effect,

lalu mengirim **MP4 60 FPS** yang siap upload.

Semua diproses di dalam Claude (FFmpeg + Remotion + speech-to-text lokal). Tidak perlu install apa pun di
laptop dan tidak perlu ngerti kode.

## Yang bikin beda
- **Bukan template.** Setiap video dirancang dari konsep naskahnya sendiri: judul punya banyak varian (di belakang
  kepala, rata kiri/kanan, ditumpuk ala poster, "slam", geser, mengetik), scene digambar sesuai cerita, dan transisinya
  bervariasi per video. Dua video dengan gaya yang sama tetap terasa satu keluarga, tapi komposisinya beda.
- **Brand kit.** Simpan warna, font, nama brand, dan kalimat CTA-mu sekali dalam satu file. Kirim file itu bersama
  video berikutnya, dan semua video kamu otomatis konsisten dengan identitas brand-mu.
- **Anti-slop.** Skill ini menolak kebiasaan edit "kelihatan AI": teks dobel (judul + subtitle sama), teks yang
  menutupi mata/mulut, kata yang tidak kamu ucapkan, ikon/emoji di mana-mana, efek tanpa alasan, sound effect di
  setiap kata, dan grafis yang lewat terlalu cepat untuk dibaca. Rencana edit dicek otomatis sebelum dirender.
- **Gerak setara motion designer.** Motion blur searah gerakan, kurva easing Bezier, teks yang muncul dari mask,
  judul "slam", coretan tangan yang tergambar, transisi zoom-through/whip/flash/light leak, film grain dan
  vignette per gaya.
- **Ritme editor asli.** Tidak ada momen diam terlalu lama, hook bergerak di 1,5 detik pertama, potongan jeda
  natural (kata tidak terpotong).
- **10 gaya yang benar-benar beda,** masing-masing dengan font, subtitle, grafis, transisi dan end card sendiri -
  bukan satu template ganti warna.

## Yang dibutuhkan
- Paket Claude yang bisa memakai **Skills** dan **code execution / pembuatan file**.
- Akses jaringan code execution yang mengizinkan package manager (npm, PyPI) dan GitHub. Dipakai di awal untuk
  mengunduh model transkripsi (Â±860 MB) dan mesin render. Kalau GitHub diblokir, skill tetap jalan asal kamu
  menempelkan teks script-nya.
- Paling nyaman dipakai di laptop/desktop.

## Cara pasang
1. Unggah `aibijan-video-editor.zip` sebagai skill di pengaturan Skills Claude.
2. Pastikan code execution aktif.
3. Buka chat baru, upload video, lalu tulis permintaanmu.

## Cara pakai
Contoh permintaan:
- "Edit video ini pakai gaya IMPACT buat iklan Reels."
- "Potong jedanya, kasih subtitle, zoom di bagian penting." â†’ Claude akan menanyakan gayanya dulu.
- "Bikin versi EDITORIAL dan versi CREATOR POP dari video yang sama."
- "Pakai MOGRAPH MIX, selingi sama animasi biar nggak muka terus."
- "MOGRAPH MIX versi flat aja, jangan pakai gradasi."
- "Ini script storytelling 'bayangin kalauâ€¦', pakai STORYTELLER, mood-nya ikutin tanda [kaget] [bisik] di script."
- "Gaya terserah, yang penting rapi." â†’ Claude memilihkan gaya yang paling cocok dengan isi videomu.
- "Ini ada 3 foto produk, masukin sebagai b-roll pas aku nyebut produknya."
- "Pakai lagu ini sebagai musiknya." (lagu milikmu / yang kamu punya lisensinya)
- "Tulis nama aku 'Rina â€” Founder' di awal."
- "Taruh tulisan besar di belakang kepalaku pas hook." / "Pas bagian penutup, ganti background-nya biar aku keluar."
- "Pakai gaya LUMEN, bikin glowing kayak iklan course." / "Pakai POSTER, background-nya biru aja."
- "Pakai gaya DEPTH." / "Bikin edit ala After Effects Reels premium: teks di belakang badan, elemen di sekitar kepala."
- "Pakai NOTED, yang simpel aja tapi ada penjelasan di atas kepala." / "Pas aku nyebut 'horor, sejarah, cara kerja',
  tempel klip-klip ini di sekitar kepalaku." (kirim klipnya)
- "Pas aku bilang 'lagi dan lagi', bikin kata-katanya muter di kepalaku." / "Kasih coretan tangan pas aku bilang 'ternyata'."
- "Simpan brand kit aku: warna oranye #E2572B dan hijau tua, nama toko Kopi Senja, CTA 'pesan lewat link di bio'."
- "Ini brand kit aku, edit video ini pakai itu." (kirim file brand kit-nya)
- "Bikin yang beda dari video kemarin, lebih kreatif." / "Simpel aja, jangan aneh-aneh."
- "Di detik 5 animasinya kecepetan, perlambat ya." â†’ Claude merender ulang bagian itu saja.
- "Transisinya jangan banyak-banyak." / "Yang 'eh' di detik 12 jangan dipotong, itu bagian lucunya."

## 10 gaya edit
| # | Gaya | Font | Cocok untuk |
|---|---|---|---|
| 1 | **IMPACT** â€” energik & tegas: judul "slam", subtitle kartu putih + sorotan lime, teks raksasa di belakang kamu | Anton + Montserrat | iklan, promo, hook jualan, bisnis |
| 2 | **SYSTEM** â€” nuansa tech/AI: subtitle terminal, prompt & chat yang mengetik sendiri | Geist + Geist Mono | tutorial tools, AI, coding, aplikasi |
| 3 | **EDITORIAL** â€” sinematik & premium: letterbox, grading film, judul serif ala sampul majalah | Playfair Display + Poppins | edukasi tenang, dokumenter, profil |
| 4 | **CREATOR POP** â€” subtitle outline tebal, stiker die-cut, split-face, cutout badan dengan outline putih | TikTok Sans | personal brand, tips cepat, lifestyle |
| 5 | **MOGRAPH MIX** â€” wajah + scene motion graphic bergantian, HP 3D, transisi orb & bars, animasi ease in/out yang halus. Dua tampilan: **aurora** (cahaya + UI kaca + gradient) atau **flat** (kertas, hitam-oranye-biru solid, tanpa gradasi) | Bricolage Grotesque + Manrope | jualan/UMKM, explainer produk, workflow, demo aplikasi, kelas online |
| 6 | **STORYTELLER** â€” storytelling sinematik: mood & warna berubah ikut emosi (kaget, bisik, serius, gelap), search bar & ikon 3D di atas kepala, kata 3D di belakang badan, kaca pembesar, subtitle mengetik, transisi cahaya & iris ke hitam | Unbounded + Plus Jakarta Sans | "bayangin kalauâ€¦", what-if, opini, fakta unik, cerita dramatis |
| 7 | **DEPTH** â€” ala After Effects Reels premium: teks raksasa di belakang badan (grotesk tebal dicampur serif italic), kata-kata mengorbit kepala, doodle tangan di sekitar kepala, dinding feed, grading terang & bersih, subtitle kalem yang muncul dari blur | Inter Tight + Instrument Serif | personal brand, kreator, tips & opini, konten "aesthetic" |
| 8 | **LUMEN** â€” sinematik & bercahaya: grading hangat-gelap, judul gradient yang menyala dengan kilau cahaya lewat, campuran huruf tebal + tulisan tangan + kicker berspasi, koin & centang 3D mengkilap yang berputar, pill kaca dengan badge centang | Outfit + Ms Madi | bisnis/UMKM, uang, produktivitas, tools, personal brand premium |
| 9 | **POSTER** â€” tiap momen penting jadi poster: latar diganti bidang warna yang terbuka dari kepala, judul tebal dua warna di belakang badan dengan gema outline, badge ikon melingkari kepala, kartu miring di samping kepala, checklist di atas frame yang di-blur | Archivo Black | edukasi, tips, review, podcast, "kayak thumbnail" |
| 10 | **NOTED** â€” simpel & jelas, kayak catatan di sekitar kepala: label mono yang mengetik sendiri, selotip kertas ditempel pas kamu sebut, coretan spidol untuk "dulu vs sekarang", kartu catatan bergaris untuk daftar, kata kunci raksasa yang distabilo, klip kamu ditempel di sekitar kepala, subtitle kalem + stabilo kuning | Geist + Geist Mono + Inter Tight | edukasi, penjelasan, tips, daftar & perbandingan, review, "simpel aja tapi informatif" |

Cukup sebut nama atau nomornya. Kalau belum menyebut gaya, Claude akan menanyakannya sekali.

## Pengaturan lain (opsional)
Sebut saja di chat: rasio 9:16 / 16:9 / 1:1 / 4:5 Â· target durasi Â· intensitas edit (low/medium/high) Â· musik
on/off atau lagu sendiri Â· sound effect halus/sedang/energik Â· jumlah grafis sedikit/sedang/banyak Â· transisi
otomatis/tanpa transisi Â· potong "eh" on/off Â· mode kreatif on/off (scene khusus per video) Â· brand kit Â· warna brand
kamu. Semua gaya selalu dirender **60 FPS**.

## Hasil yang bisa diharapkan
- Jeda, "eh/emm" dan kalimat ulang dipotong natural, audio dan video tetap sinkron.
- Subtitle sesuai ucapan asli, sinkron per kata, aman dari area tombol TikTok/Reels.
- Suara aslimu tetap dipakai: dibersihkan dan diratakan, bukan diganti AI.
- Grafis yang menjelaskan isi omongan, tidak menutupi wajah, dan tidak mengarang fakta.
- Loudness âˆ’14 LUFS dan MP4 H.264, siap upload.
- Setiap hasil dicek otomatis: 60 FPS, durasi, sinkron suara, sinkron subtitle, loudness, frame hitam, anti-slop, ritme.

## Batasan yang jujur
- Butuh rekaman dengan suara orang yang jelas. Musik keras di belakang suara menurunkan akurasi subtitle.
- Footage 30 FPS: gerakan wajah/kamera tetap 30 FPS (frame diulang), sedangkan subtitle, grafis, zoom dan
  transisi dihitung penuh di 60 FPS. Tidak ada interpolasi frame AI.
- Render 60 FPS dengan motion blur butuh waktu: sekitar 3 frame per detik, jadi video 30 detik â‰ˆ 9â€“10 menit
  render (dikerjakan bertahap, Claude melanjutkannya sendiri). Video yang lebih panjang butuh waktu sebanding.
- Background remove butuh Â±1 detik proses per frame, jadi dipakai di momen pilihan saja (hook, payoff), bukan
  sepanjang video. Hasil terbaik dengan background polos dan pencahayaan rata.
- Di chat baru, model transkripsi dan mesin render diunduh ulang (beberapa menit di awal).
- Bukan untuk mengganti suara, membuat avatar, menghapus objek, tracking 3D, atau color grading sinematik penuh.

## Kalau ada kendala
- **Model tidak bisa diunduh** â†’ tempelkan teks script kamu; Claude menyelaraskannya dengan audio.
- **Model background remove diblokir** â†’ video tetap jadi; teks yang direncanakan di belakang badan tampil di depan.
- **Render "belum selesai"** â†’ normal untuk video panjang; Claude melanjutkan sendiri.
- **Nama brand salah eja di subtitle** â†’ sebut ejaan yang benar; Claude memperbaikinya.
- **File terlalu besar untuk diupload** â†’ potong dulu atau export ulang di 1080p.
- **Lingkungan tanpa browser** â†’ Claude memakai mesin cadangan (potong, zoom, subtitle, suara tetap ada; tanpa
  motion graphic) dan memberi tahu kamu.

## Lisensi & pihak ketiga
- Font: Anton, Montserrat, Geist, Geist Mono, Playfair Display, Poppins, TikTok Sans, Bricolage Grotesque, Manrope, Unbounded, DM Sans,
  Space Grotesk, Plus Jakarta Sans, Inter Tight, Instrument Serif, Outfit, Archivo, Ms Madi â€” semuanya SIL Open Font License (aman dipakai dan dibundel di produk komersial).
- Background remove: model "silueta" (berbasis UÂ²-Net) dari proyek rembg, diunduh dari GitHub saat pertama dipakai
  dan dijalankan lokal dengan onnxruntime.
- Musik bawaan dan SFX dibuat secara prosedural oleh skill ini (tanpa sampel pihak ketiga). Lagu yang kamu
  upload sendiri adalah tanggung jawabmu (pastikan kamu punya hak memakainya).
- Speech-to-text: Whisper large-v3-turbo (MIT) dan Omnilingual-ASR CTC (Apache-2.0) via sherpa-onnx (Apache-2.0).
- Remotion (diinstal dari npm saat render): gratis untuk individu dan organisasi sampai 3 orang; perusahaan
  yang lebih besar memerlukan lisensi Remotion sendiri (remotion.dev/license).
- Footage dan aset yang kamu kirim tetap milikmu dan tanggung jawabmu.
