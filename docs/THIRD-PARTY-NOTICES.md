# Third-Party Notices

Fail ini menyenaraikan komponen pihak ketiga yang diguna atau dijadikan asas
dalam AI BIJAN SaaS, dengan notis lesen yang dikehendaki.

---

## 1. B-Pos System / `aryadwiputra/point-of-sales` (asas modul POS)

Ciri POS dalam `app/Modules/POS/` (BAB 88: dine-in, modifier, caj perkhidmatan,
tender berbilang, syif tunai, TNG soundbox, kelulusan diskaun) dijadikan asas
daripada **B-Pos System** (`b-pos-system/`).

### Status lesen sebenar

| Lapisan | Lesen | Pemilik |
|---------|-------|---------|
| Projek huluan `aryadwiputra/point-of-sales` | **MIT** | Arya Dwi Putra (2025) |
| B-Pos System (jenama + lapisan tambahan: DOKU, TnG, PWA, dll.) | **Proprietari** | Bijan Digital Marketing |
| AI BIJAN SaaS | **Proprietari** | Bijan Digital Marketing |

**Kedua-dua B-Pos System dan AI BIJAN dimiliki oleh Bijan Digital Marketing**,
jadi pemindahan ciri antara keduanya ialah urusan dalaman pemilik yang sama dan
tidak memerlukan kebenaran pihak ketiga.

**Bagaimanapun**, kerana bahagian huluan adalah MIT, notis MIT di bawah **mesti
dikekalkan** dalam salinan atau bahagian substansial mana-mana perisian turunan.
Notis ini dipetik daripada `b-pos-system/LICENSE`.

### Notis MIT (dikekalkan seperti diperlukan)

```
MIT License
Copyright (c) 2025 Arya Dwi Putra

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

### Perubahan yang dibuat semasa pemindahan

Kod yang dipindahkan telah **ditulis semula** mengikut konvensyen AI BIJAN,
bukan disalin terus (lihat `docs/TODO.md` §Nota Integrasi B-POS). Perbezaan
utama: `tenant_id` (bukan `outlet_id`), penamaan Bahasa Melayu, token awam
ber-HASH + luput, dan pengesahan silang-tenant bagi setiap ID.

### Penyelenggaraan serasi kontrak (2026-10-28)

Sumber sedia ada telah diperiksa. Perubahan ini **bukan clean-room**, bukan
bukti asal-usul bebas, dan tidak menghapuskan kewajipan notis di atas.
Tiada sumber luar disalin atau dinyahkompil semasa perubahan ini.

- `TenderService`: normalisasi satu tender tidak lagi mengubah pembilang
  melalui rujukan; pengesahan senarai dikira daripada hasil normalisasi.
  Penggabungan status kini menggunakan ruang tetap, bukan menyimpan semua
  status. Iterable tetap dihabiskan, termasuk selepas status `failed`.
- `ModifierService`: bilangan pilihan setiap kumpulan dikira sekali, bukan
  menapis semua pilihan berulang kali bagi setiap kumpulan. Susunan snapshot
  sedia ada sengaja dikekalkan, termasuk tingkah laku pembanding Laravel.
- `ServiceChargeService`: dokumentasi diperbetulkan sahaja. Kontrak awam
  `kadar/skop/terpakai/kira`, kunci `amount/rate`, dan konfigurasi global kekal.
- `DineOrderService`, `KitchenService`, `SalePaymentService`, bahagian pindahan
  `CashShiftService`, `TngSoundboxService`, dan `DiscountApprovalService`
  dikekalkan. Aliran transaksi, cap masa, pertanyaan dan pengesahannya sudah
  terasing; pengekstrakan/penamaan semula sahaja tidak membuktikan penggantian
  substantif. Perubahan replay, penguncian, pemadanan atau skop konfigurasi
  akan mengubah tingkah laku yang pengguna memilih untuk kekalkan.

Ini penambahbaikan dalaman terhad pada dua servis, **bukan penggantian penuh
kesemua sembilan servis**. Skema, laluan, format respons, pembundaran dan
pengesahan tidak diubah. Jualan selesai masih ditolak sebelum carian UUID;
jualan draf dengan UUID jualan selesai masih mengembalikan jualan terdahulu.

Enam ujian pencirian ditambah dahulu dalam `BposPaymentTest` dan
`BposOperationTest`, lalu diluluskan terhadap implementasi sebelum perubahan:
normalisasi tender (enam kaedah, kunci, pembundaran, masa), keutamaan ralat,
iterable/status tidak dikenali, kedua-dua laluan replay, konfigurasi caj
langsung, dan deduplikasi/susunan/snapshot modifier. Ujian sedia ada meliputi
operasi POS serta pengasingan tenant; ini bukan bukti menyeluruh untuk
perlumbaan serentak atau semua keadaan pangkalan data produksi.

---

## 2. Komponen lain

Notis untuk pakej Composer/npm pihak ketiga lain dikendalikan oleh fail
`licenses` pakej masing-masing dan tidak disenaraikan di sini.
