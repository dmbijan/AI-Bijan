# Brand kit AI BIJAN

Satu file, `aibijan-brand.json`, untuk semua skill AI BIJAN (Video Editor, Mograph, Motion 3D). Isinya nama
channel, handle, ajakan (CTA), warna, font, dan logo. Simpan sekali, pakai di setiap video.

## Cara membuatnya

Paling gampang, minta Claude:

> Buatin brand kit AI BIJAN aku: nama channel Dapur Hemat, handle @dapurhemat, warna utama biru tua, aksen oranye,
> CTA "Follow buat tips hemat lainnya".

Claude akan membuat file `aibijan-brand.json`. Simpan file itu (dan logomu, kalau ada) di satu folder.

Atau tulis sendiri dengan format ini:

```json
{
  "version": 1,
  "brand": "Dapur Hemat",
  "handle": "@dapurhemat",
  "cta": "Follow buat tips hemat lainnya",
  "colors": {
    "primary": "#1B2A4A",
    "accent": "#FF8A00",
    "background": "#FFF8EC"
  },
  "font": "Plus Jakarta Sans",
  "logo": "logo.png"
}
```

| Kolom | Isi | Wajib? |
|---|---|---|
| `brand` | nama channel atau merek | disarankan |
| `handle` | akun media sosial, misalnya `@dapurhemat` | tidak |
| `cta` | ajakan di akhir video | tidak |
| `colors.primary` | warna utama, sebaiknya gelap (untuk latar gelap dan teks) | tidak |
| `colors.accent` | warna sorotan, sebaiknya cerah (untuk kata penting dan grafik) | tidak |
| `colors.background` | warna latar terang | tidak |
| `font` | Plus Jakarta Sans, DM Sans, Space Grotesk, atau Instrument Serif | tidak |
| `logo` | nama file logo (PNG transparan) di folder yang sama | tidak |

Warna ditulis dalam format hex, contoh `#FF8A00`. Kalau belum tahu kode warnanya, cukup sebut warnanya ke Claude.

## Cara memakainya

- **AI BIJAN Video Editor** (chat Claude): upload `aibijan-brand.json` bersama videomu, lalu tulis
  "pakai brand kit aku".
- **AI BIJAN Mograph** (chat Claude atau Claude Code): lampirkan `aibijan-brand.json` bersama VO dan naskah, atau
  taruh di folder kerja. Claude memakainya otomatis.
- **AI BIJAN Motion 3D** (Claude Code): taruh `aibijan-brand.json` di folder kerja atau lampirkan di chat.

## Apa yang diterapkan di tiap skill

- **Video Editor:** warna aksen untuk sorotan subtitle dan grafis, warna utama untuk tombol dan elemen kedua, font
  (kalau termasuk empat font bawaan), serta nama, handle, dan CTA untuk kartu penutup dan nama di layar. Logo belum
  ditempel otomatis.
- **Mograph:** warna utama, aksen, dan latar dipakai di seluruh adegan dan komponen; nama channel tampil di layar;
  logo bisa dipakai di pembuka atau penutup.
- **Motion 3D:** nama, handle, dan CTA di teks judul dan penutup; warna aksen untuk label dan sorotan kalau cocok
  dengan palet gayanya. Warna dunia 3D tetap mengikuti gaya, kecuali kamu memintanya.

## Supaya tetap terbaca

Claude mengecek kontras setiap warna. Warna yang membuat teks susah dibaca tidak dipakai di posisi itu, dan Claude
memberi tahu alasannya dalam satu kalimat. Hasil terbaik: warna utama yang gelap dan aksen yang cerah.
