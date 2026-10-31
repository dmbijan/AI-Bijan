## 12. Standard UI/UX: lebih mudah daripada Odoo

**Siapa pengguna kita:** pemilik kedai dan staf di Malaysia, kebanyakannya guna **telefon**, bukan pakar sistem. Odoo direka untuk pengguna terlatih yang dibantu konsultan. AI Bijan mesti boleh dipakai **tanpa latihan**.

### 12.1 Sepuluh prinsip

1. **Telefon dahulu.** Setiap skrin mesti boleh dipakai di skrin 390 px dengan satu tangan. Butang sekurang-kurangnya 44 px.
2. **Satu tindakan utama per skrin.** Butang utama jelas (contohnya "Bayar"); tindakan lain sekunder.
3. **BM santai, bukan bahasa sistem.** "Stok tak cukup", bukan "Insufficient quantity on hand". EN tersedia, tukar satu ketik.
4. **WhatsApp sebagai antara muka kedua.** Resit, laporan harian, kelulusan, peringatan dan tempahan boleh dibuat terus di WhatsApp tanpa buka app.
5. **Tanya, jangan cari.** Kotak "Tanya AI Bijan" di setiap skrin: "jualan hari ni?", "siapa belum bayar?". Jawapan beri angka + pautan ke rekod.
6. **AI cadang, manusia putus.** Cadangan AI dipaparkan sebagai kad dengan butang **Lulus / Ubah / Tolak**, berserta sebab ringkas dan kesan (RM, kredit).
7. **Tak pernah hilang kerja.** Offline-first untuk kaunter (dah dibuat, PR #2), draf autosimpan, undo untuk tindakan risiko rendah.
8. **Mula dengan contoh, bukan kosong.** Akaun baharu terus ada produk, kaunter dan laporan contoh mengikut jenis bisnes (Ejen Penyediaan); boleh dibuang satu klik.
9. **Ralat yang membantu.** Setiap mesej ralat beritahu apa berlaku dan apa nak buat ("Tiada gudang untuk kaunter ini → Tetapkan gudang").
10. **Laju.** Skrin utama < 2 s di 4G; tindakan kaunter < 300 ms; tiada spinner tanpa teks.

### 12.2 Perbandingan dengan Odoo

| Situasi | Odoo | AI Bijan |
|---|---|---|
| Mula guna | Pasang app, konfigurasi, latihan | Ejen Penyediaan tanya 5 soalan, sistem siap dengan data contoh |
| Cari maklumat | Menu → laporan → penapis | Tanya dalam BM |
| Kelulusan | Buka app Approvals | Ketik "Lulus" di WhatsApp |
| Kaunter tanpa internet | Bergantung pada versi/peranti | Jual terus, segerak kemudian tanpa pendua |
| Ubah borang | Studio (perlu faham model data) | Terangkan dalam ayat; pratonton; lulus |

### 12.3 Ukuran kejayaan UX

| Metrik | Sasaran |
|---|---|
| Masa daftar → jualan pertama | < 30 min |
| Tugas utama (jual, terima stok, hantar invois) | ≤ 3 ketik dari skrin utama |
| Pengguna aktif harian / pengguna berdaftar | > 60% |
| Cadangan AI yang diluluskan | > 50% (petanda cadangan berguna) |
| Tiket sokongan "macam mana nak…" per 100 syarikat/bulan | Menurun bulan ke bulan |

### 12.4 Langkah UI segera (sejajar MVP-1)

1. Skrin utama baharu: **4 kad sahaja** (Jualan hari ni, Duit belum kutip, Stok perlu tindakan, Cadangan AI untuk diluluskan).
2. **Peti Kelulusan** tunggal (app + WhatsApp) untuk semua cadangan ejen.
3. Kotak **Tanya AI Bijan** global (Ctrl+K / butang mikrofon di telefon).
4. Semak semua skrin sedia ada: telefon 390 px, tema cerah/gelap, BM, mesej ralat; gunakan proses `cek-tampilan` sebelum serah.
