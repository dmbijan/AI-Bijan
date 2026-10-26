# AI Bijan — Command Center Fasa 1 (gaya RekaDesk)

Mockup siap-pasang. Bila kod AI Bijan sebenar sampai (GitHub atau zip ringan), ini tinggal lekat.

## Apa ni
Satu fail `command-center.html` — halaman utama "sekali pandang":
- **Jalur Langsung** (meniru Live Activity dalam app) — cth kempen WhatsApp sedang dihantar.
- **Kad 1 — Duit belum kutip:** jumlah RM + senarai piutang + butang **Ingatkan** (akan jadi draf WhatsApp; hantar = owner lulus).
- **Kad 2 — Seterusnya:** tempahan/tugas akan datang + kiraan masa + lokasi.
- **Kad 3 — Tugas minggu ni:** % siap + bar + senarai tugas ikut divisi.
- **Suis tema cerah/gelap** (kekal guna localStorage).
- Mobile-first; laptop auto jadi 2-lajur; bar navigasi bawah di HP.

## Warna jenama (dah dipakai)
- Aksen amber: `#e8a33d` · gelap coklat: `#241b16` · latar hangat: `#f6f3ec`
- Tema gelap: latar `#15110c`, kad `#201913`, aksen `#f0ad48`

## Cara masukkan ke AI Bijan (bila kod ada)
1. Jadikan 3 kad = komponen (Blade/React) yang baca data sebenar:
   - Kad 1 ← B-Billing (invois belum bayar / lewat tempoh).
   - Kad 2 ← B-Booking / kalendar tugas.
   - Kad 3 ← senarai tugas minggu (Brain Hub / modul tugas).
   - Jalur Langsung ← status kempen B-Marketing yang sedang jalan.
2. Pindahkan blok `:root` + `[data-tema="gelap"]` jadi token tema global aplikasi.
3. Butang **Ingatkan** → panggil draf mesej WhatsApp; hantar ikut pagar (owner lulus dulu).

## Status
- Fasa 1 = papan sekali pandang (INI).
- Fasa 2 = alur sebut harga → deposit (FPX/DuitNow) — perlu gateway MY.
- Fasa 3 = jadual krew (siapa pergi mana).

Data dalam mockup CONTOH sahaja.
