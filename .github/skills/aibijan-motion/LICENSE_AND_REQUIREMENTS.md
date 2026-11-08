# Lisensi dan persyaratan

## Persyaratan perangkat lunak

| Perangkat lunak | Versi | Siapa yang memasang | Catatan |
|---|---|---|---|
| Claude Code | terbaru | kamu | Butuh paket Claude berbayar (Pro/Max/Team/Enterprise) atau akun API Anthropic |
| Node.js + npm | ≥ 22.18 untuk CINÉSTORY 3D; ≥ 22.13 untuk gaya lain | kamu | <https://nodejs.org> |
| ffmpeg + ffprobe | terbaru | kamu | Analisis VO, contact sheet, audio final |
| Remotion 4.0.539 | dikunci di proyek | Claude, lewat `npm ci` | Kerangka render video |
| three.js 0.186.1, React 19.2.3, @react-three/fiber 9.8.2, TypeScript 5.9.3 | dikunci di proyek | Claude, lewat `npm ci` | Mesin 3D dan bahasa kode |
| Font Bangers, Luckiest Guy, Comic Neue | SIL Open Font License 1.1 | Disertakan di `kits/panelverse/public/fonts` (lisensi: `OFL-pv-*.txt`); boleh dipakai di video |
| Chrome Headless Shell | dipilih Remotion | otomatis saat render pertama | ±170 MB per proyek |

Komputer yang disarankan: **Mac dengan chip Apple (M1 ke atas)** atau PC dengan kartu grafis (GPU). Tanpa GPU, render 3D jauh lebih lambat.

## Lisensi pihak ketiga

| Komponen | Lisensi | Yang perlu kamu ketahui |
|---|---|---|
| **Remotion** | Remotion License (bukan open-source murni) | Saat dokumen ini ditulis, Remotion gratis untuk perorangan, organisasi nirlaba, dan perusahaan dengan maksimal 3 karyawan. **Perusahaan yang lebih besar wajib membeli Remotion Company License.** Cek ketentuan terbaru di <https://remotion.dev/license> sebelum memakai video untuk usaha. |
| three.js | MIT | Bebas dipakai |
| React, React DOM | MIT | Bebas dipakai |
| @react-three/fiber | MIT | Bebas dipakai |
| TypeScript | Apache-2.0 | Bebas dipakai |
| Font Manrope | SIL Open Font License 1.1 | Disertakan di `starter/public/fonts` (lisensi: `OFL-manrope.txt`); boleh dipakai di video |
| Font Anton, Instrument Serif, Silkscreen, Space Grotesk | SIL Open Font License 1.1 | Disertakan di `kits/pixelstory/public/fonts` (lisensi di folder `licenses`); boleh dipakai di video |
| Font Bangers, Luckiest Guy, Comic Neue | SIL Open Font License 1.1 | Disertakan di `kits/panelverse/public/fonts` (lisensi: `OFL-pv-*.txt`); boleh dipakai di video |
| Chrome Headless Shell | Lisensi Chromium/Google | Diunduh otomatis oleh Remotion |
| ffmpeg | LGPL/GPL (tergantung build) | Dipasang sendiri olehmu |
| whisper.cpp (opsional, CHARACTER FLOW, CINÉSTORY, dan PANELVERSE) | MIT | Dipasang sendiri olehmu |
| Node.js | MIT | Dipasang sendiri olehmu |

## Aset

- Paket ini **tidak** berisi musik, voice-over, model 3D, foto, atau logo pihak ketiga. Semua geometri dan tekstur dibuat secara prosedural oleh kode.
- Gambar di `references/quality-bar/` adalah cuplikan render asli milik pembuat AI BIJAN Motion. Gambar itu hanya acuan kualitas untuk Claude dan **tidak** untuk dipakai ulang di videomu.
- Voice-over (misalnya ElevenLabs), musik, logo, dan gambar yang kamu berikan harus milikmu atau berlisensi untuk penggunaanmu. Periksa syarat paket ElevenLabs-mu untuk pemakaian komersial.
- Jangan meminta tiruan merek, produk, karakter, atau game yang dilindungi hak cipta.
- CHARACTER FLOW, CINÉSTORY 3D, dan PANELVERSE: tokoh, lokasi, benda, musik, dan efek suara dibuat sepenuhnya oleh kode (sintesis). Suara TTS dari macOS (`say`) disediakan Apple untuk komputermu; untuk video komersial, VO rekaman/berlisensi lebih disarankan.

## Lisensi AI BIJAN Motion

- Kamu boleh memakai skill ini untuk membuat video, baik pribadi maupun komersial. Video yang kamu hasilkan menjadi milikmu, dengan tetap mematuhi lisensi pihak ketiga di atas, terutama Remotion.
- Skill, kode, dan dokumen di dalam paket ini **tidak boleh dijual ulang, dibagikan ulang, atau diunggah publik** tanpa izin tertulis dari penjual.
- Paket disediakan apa adanya. Hasil video bergantung pada ide, perangkat, dan iterasi, dan tidak dijamin sama persis dengan contoh.
- Animasi konstruksi (BUILDFORGE) bersifat ilustrasi, **bukan** panduan teknik. Desain masa depan adalah **konsep**, bukan prediksi.
