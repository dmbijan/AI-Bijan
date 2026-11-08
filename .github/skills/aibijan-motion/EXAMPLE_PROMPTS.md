# Contoh prompt

Ketik setelah `/aibijan-motion`. Nama gaya tidak wajib ditulis; Claude memilihnya sendiri.

CINÉSTORY 3D, CHARACTER FLOW, PANELVERSE, keenam gaya dengan mesin khusus, dan dua gaya dengan mesin inti bisa dipakai semua; PRECISION dan INNERWORKS biasanya perlu satu putaran preview tambahan (lihat `START_HERE.md`).

| Gaya | Hasilnya seperti | Contoh prompt |
|---|---|---|
| **PIXELSTORY** | kisah karakter pixel-art sinematik, 2.5D | "Animasi pixel tentang penjaga mercusuar yang menyalakan lampu saat badai. Musik saja." |
| **VOXELVERSE** | dunia miniatur 3D dari balok voxel | "Kota mini voxel saat malam hujan, robot kecil mengantar surat bercahaya. Aku punya VO." |
| **PRECISION** | iklan produk 3D premium | "Iklan 3D botol minum fiktif, close-up material, bagian-bagiannya terpisah lalu menyatu." |
| **PAPERWORLD** | cerita miniatur dari kertas | "Cerita kertas tentang tukang kebun kecil yang mengembalikan warna ke kota kertas." |
| **INNERWORKS** | membedah cara kerja benda | "Cara kerja kipas angin listrik, casingnya terbuka dan bagian dalamnya kelihatan. VO menyusul." |
| **WORLDFORGE** | menjelajahi dunia 3D | "Bayangkan kota di dasar laut dengan kubah kaca dan terowongan transparan. Ini MP3-nya: @/path/narasi.mp3" |
| **TIMEFORGE** | satu benda berevolusi dari masa ke masa | "Evolusi televisi dari TV tabung sampai TV tipis sekarang." |
| **BUILDFORGE** | bangunan dirakit tahap demi tahap | "Rumah dua lantai dibangun dari tanah kosong sampai jadi, fondasi, rangka, dinding, atap, taman." |
| **CINÉSTORY 3D** (#09) | film pendek 3D sinematik dari voice-over, di lokasi yang dibangun untuk ceritanya | "Buatkan video CINÉSTORY 3D dari VO ini: @/Users/kamu/Downloads/vo.mp3 (naskahnya: @/Users/kamu/Downloads/naskah.txt)" |
| **CHARACTER FLOW** (#10) | tokoh 3D ekspresif memerankan voice-over | "Buatkan video CHARACTER FLOW dari VO ini: @/Users/kamu/Downloads/vo.mp3" |
| **PANELVERSE** (#11) | komik pop retro yang hidup: panel, halftone, tulisan komik, efek suara | "Buatkan video PANELVERSE dari VO ini: @/Users/kamu/Downloads/vo.mp3" |

## Dengan naskah + voice-over

```
/aibijan-motion Ini naskah dan voice-over untuk video tentang evolusi kamera.
Naskah: (tempel naskahmu)
VO: @/Users/kamu/Downloads/vo-kamera.mp3
```

## CINÉSTORY 3D — cukup satu kalimat

```
/aibijan-motion Buatkan video CINÉSTORY 3D dari VO ini: /Users/kamu/Downloads/vo.mp3 (naskahnya: /Users/kamu/Downloads/naskah.txt)
```

- Sedikit arahan (opsional): "Tokohnya perempuan berhijab, latar kos malam hari." · "Akhiri dengan suasana pagi yang hangat."
- Versi YouTube: tambahkan "versi landscape 16:9" (subtitle-nya berupa file `.srt`, kecuali kamu minta ditempel di video).
- Dengan brand kit: "pakai brand kit aku (aibijan-brand.json)" — nama channel, logo, dan ajakan di penutup video.
- Belum pasang whisper.cpp? Sertakan teks naskah yang sama persis dengan VO.
- Revisi: "Di bagian lampu laut, kabutnya lebih tebal." · "Tambahkan close-up saat dia kaget." · "Ganti tokohnya jadi bapak berkacamata." · "Subtitle-nya matikan saja (cukup file .srt)."

## CHARACTER FLOW — cukup satu kalimat

```
/aibijan-motion Buatkan video CHARACTER FLOW dari VO ini: /Users/kamu/Downloads/vo.mp3
```

- Mau sedikit mengarahkan? Tambahkan: "Tokohnya cewek." · "Di kamar malam hari." · "Dua tokoh di kampus, dibuat lucu." · "Di kafe, tokohnya cewek berhijab." · "Pindah-pindah: rumah, sekolah, kantor."
- Belum pasang whisper.cpp (lihat `INSTALLATION_GUIDE.md`)? Sertakan teks naskahnya: `Naskahnya: …`
- Belum punya VO? "Aku mau video CHARACTER FLOW tentang susah bangun pagi, tapi belum punya VO." Claude menawarkan naskah + suara TTS dan baru membuatnya setelah kamu setuju.
- Revisi setelah video jadi: "Di bagian 'tapi tangan malah membuka HP', ambil gambar lebih dekat." · "Tokohnya ganti jadi Raka." · "Suasananya sore." · "Musiknya lebih hangat."

## PANELVERSE — cukup satu kalimat

```
/aibijan-motion Buatkan video PANELVERSE dari VO ini: /Users/kamu/Downloads/vo.mp3
```

- Cocok untuk cerita relatable yang punya "belokan": overthinking, ekspektasi vs realita, "pernah ga…", daftar kemungkinan, lalu punchline ("padahal…", "ternyata…").
- Mau sedikit mengarahkan? Tambahkan: "Tokohnya ibu-ibu berhijab." · "Endingnya dicap LUNAS." · "Bagian tiga alasan dibuat tiga panel." · "Warnanya dominan merah-kuning."
- Belum pasang whisper.cpp? Sertakan teks naskahnya: `Naskahnya: …`
- Belum punya VO? "Aku mau video PANELVERSE tentang gaji yang cepat habis, tapi belum punya VO." Claude menawarkan naskah + suara TTS dan baru membuatnya setelah kamu setuju.
- Revisi: "Panel pertama ganti jadi layar terbelah." · "Tulisan BOROS lebih besar." · "Tambahkan suara uang jatuh saat kata 'habis'." · "Ending-nya ditahan lebih lama."

## Revisi setelah preview

- "Bagian terowongan terlalu cepat, perlambat."
- "Tambahkan judul di awal: 'KOTA DI DASAR LAUT'."
- "Warnanya lebih hangat."
- "Ending-nya tahan lebih lama sebelum fade."
- "Oke, render final."
