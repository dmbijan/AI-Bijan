/* AI Bijan POS — mod luar talian + cetak resit thermal (ESC/POS melalui WebUSB).
 * Dibawa dari B-POS lama (Next.js) dan ditulis semula untuk skrin Blade.
 * Jualan luar talian disegerak ke POST /api/v1/pos/sales/checkout dengan header
 * Idempotency-Key = client_id, jadi segerak berulang tak jadi pendua. */
(function () {
    'use strict';

    // ---------- storan peranti ----------
    const K_KATALOG = 'aib-pos-katalog';
    const K_KAUNTER = 'aib-pos-kaunter';
    const K_GILIRAN = 'aib-pos-giliran';
    const K_PRINTER = 'aib-printer';

    function baca(k, lalai) {
        try { return JSON.parse(localStorage.getItem(k) || 'null') ?? lalai; } catch { return lalai; }
    }
    function tulis(k, v) {
        try { localStorage.setItem(k, JSON.stringify(v)); } catch { /* storan penuh / disekat */ }
    }

    // Ralat rangkaian (bukan ralat pelayan): fetch gagal, atau proksi tak dapat capai pelayan.
    function ralatRangkaian(e) {
        return e instanceof TypeError || [502, 503, 504].includes(e?.status);
    }

    function uuid() {
        if (crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    const LuarTalian = {
        simpanKatalog: rows => tulis(K_KATALOG, rows),
        katalog: () => baca(K_KATALOG, []),
        simpanKaunter: rows => tulis(K_KAUNTER, rows),
        kaunter: () => baca(K_KAUNTER, []),
        giliran: () => baca(K_GILIRAN, []),
        tambah(jualan) { tulis(K_GILIRAN, [...this.giliran(), jualan]); },
        buang(id) { tulis(K_GILIRAN, this.giliran().filter(j => j.client_id !== id)); },
        kemas(id, ubah) { tulis(K_GILIRAN, this.giliran().map(j => j.client_id === id ? { ...j, ...ubah } : j)); },
        ralatRangkaian,
        uuid,

        /** Hantar satu jualan. Pulangkan {ok, sale} atau {luarTalian:true}; ralat pelayan dilontar. */
        async hantar(payload, clientId) {
            try {
                const sale = await App.api.post('/api/v1/pos/sales/checkout', payload, { headers: { 'Idempotency-Key': clientId } });
                return { ok: true, sale };
            } catch (e) {
                if (ralatRangkaian(e)) return { luarTalian: true };
                throw e;
            }
        },

        /** Segerak semua jualan dalam giliran. Pulangkan bilangan yang berjaya. */
        async segerak() {
            let berjaya = 0;
            for (const j of this.giliran()) {
                try {
                    const r = await this.hantar(j.payload, j.client_id);
                    if (r.luarTalian) break; // masih tiada talian: cuba lagi kemudian
                    if (j.order_id) await App.api.post(`/api/v1/pos/table-orders/${j.order_id}/close`, { sale_id: r.sale.id }).catch(() => {});
                    this.buang(j.client_id);
                    berjaya++;
                } catch (e) {
                    // Pelayan tolak (cth. 422): simpan ralat, jangan buang — owner perlu nampak.
                    this.kemas(j.client_id, { ralat: e.message || 'Ditolak pelayan' });
                }
            }
            return berjaya;
        },
    };

    // ---------- ESC/POS ----------
    const ESC = 0x1b, GS = 0x1d;
    const lebarKertas = saiz => saiz === '58' ? 32 : 48;

    // Printer thermal guna set aksara asas; tukar aksara luar ASCII supaya tak jadi sampah.
    function ascii(s) {
        return String(s ?? '').normalize('NFKD').replace(/[̀-ͯ]/g, '')
            .replace(/[×]/g, 'x').replace(/[–—]/g, '-').replace(/[^\x20-\x7e]/g, '?');
    }
    function kiriKanan(kiri, kanan, lebar) {
        kiri = ascii(kiri); kanan = ascii(kanan);
        const ruang = lebar - kanan.length - 1;
        if (kiri.length > ruang) kiri = kiri.slice(0, Math.max(0, ruang));
        return kiri + ' '.repeat(Math.max(1, lebar - kiri.length - kanan.length)) + kanan;
    }
    function balut(teks, lebar) {
        const out = []; let baris = '';
        for (const w of ascii(teks).split(' ')) {
            if ((baris + ' ' + w).trim().length > lebar) { if (baris) out.push(baris); baris = w.slice(0, lebar); }
            else baris = (baris + ' ' + w).trim();
        }
        if (baris) out.push(baris);
        return out;
    }
    class Penulis {
        constructor(lebar) { this.lebar = lebar; this.b = [ESC, 0x40]; }
        teks(s) { for (const ch of ascii(s)) this.b.push(ch.charCodeAt(0)); this.b.push(0x0a); return this; }
        tengah(on = true) { this.b.push(ESC, 0x61, on ? 1 : 0); return this; }
        tebal(on = true) { this.b.push(ESC, 0x45, on ? 1 : 0); return this; }
        garis() { return this.teks('-'.repeat(this.lebar)); }
        suap(n = 4) { this.b.push(ESC, 0x64, n); return this; }
        potong() { this.b.push(GS, 0x56, 0x42, 0x00); return this; }
        laci() { this.b.push(ESC, 0x70, 0x00, 0x19, 0xfa); return this; } // denyut laci tunai pin 2
        bait() { return new Uint8Array(this.b); }
    }
    const wang = n => Number(n || 0).toFixed(2);

    /** r = {syarikat, kaunter, no, tarikh, luarTalian, baris:[{nama,qty,harga}], jumlah, cara, tunai, baki} */
    function binaResit(r, { saiz = '80', bukaLaci = false } = {}) {
        const L = lebarKertas(saiz), p = new Penulis(L);
        p.tengah().tebal().teks(r.syarikat || 'AI BIJAN POS').tebal(false);
        if (r.kaunter) p.teks(r.kaunter);
        if (r.luarTalian) p.tebal().teks('LUAR TALIAN').tebal(false);
        p.tengah(false).teks(`No: ${r.no}`).teks(`Tarikh: ${r.tarikh}`).garis();
        for (const b of r.baris) {
            for (const s of balut(b.nama, L)) p.teks(s);
            p.teks(kiriKanan(`  ${b.qty} x ${wang(b.harga)}`, wang(b.qty * b.harga), L));
        }
        p.garis().tebal().teks(kiriKanan('JUMLAH', `RM ${wang(r.jumlah)}`, L)).tebal(false);
        p.teks(kiriKanan(r.cara === 'duitnow' ? 'DUITNOW QR' : String(r.cara).toUpperCase(), wang(r.cara === 'cash' ? r.tunai : r.jumlah), L));
        if (r.baki > 0) p.teks(kiriKanan('Baki', wang(r.baki), L));
        p.garis().tengah().teks('Terima kasih! Datang lagi.').tengah(false).suap().potong();
        if (bukaLaci) p.laci();
        return p.bait();
    }

    /** Tiket dapur: huruf besar, tanpa harga. p = {meja, masa, pelanggan, items:[{nama,qty,nota}], nota} */
    function binaTiketDapur(p, { saiz = '80' } = {}) {
        const L = lebarKertas(saiz), w = new Penulis(L);
        const besar = on => { w.b.push(GS, 0x21, on ? 0x11 : 0x00); return w; };
        w.tengah(); besar(true).tebal().teks(p.meja); besar(false).tebal(false);
        w.teks(p.masa);
        if (p.pelanggan) w.teks(p.pelanggan);
        w.tengah(false).garis();
        for (const i of p.items) {
            besar(true).teks(`${i.qty} x ${i.nama}`); besar(false);
            if (i.nota) w.teks(`   >> ${i.nota}`);
        }
        if (p.nota) { w.garis(); for (const s of balut(p.nota, L)) w.teks(s); }
        w.suap().potong();
        return w.bait();
    }

    // ---------- WebUSB ----------
    async function bukaPeranti(dev) {
        if (!dev.opened) await dev.open();
        if (dev.configuration === null) await dev.selectConfiguration(1);
        for (const intf of dev.configuration.interfaces) {
            for (const alt of intf.alternates) {
                const keluar = alt.endpoints.find(e => e.direction === 'out' && e.type === 'bulk');
                if (keluar) {
                    if (!intf.claimed) await dev.claimInterface(intf.interfaceNumber);
                    return keluar.endpointNumber;
                }
            }
        }
        throw new Error('Printer tiada endpoint cetak (bulk OUT).');
    }

    const Printer = {
        sokong: () => !!navigator.usb,
        tetapan: () => ({ saiz: '80', autoCetak: false, bukaLaci: false, ...baca(K_PRINTER, {}) }),
        simpan: t => tulis(K_PRINTER, t),
        async sambung() {
            const dev = await navigator.usb.requestDevice({ filters: [] });
            await bukaPeranti(dev);
            return dev;
        },
        async diingat() {
            if (!this.sokong()) return null;
            return (await navigator.usb.getDevices())[0] || null;
        },
        async cetak(bait) {
            const dev = await this.diingat();
            if (!dev) throw new Error('Tiada printer disambung.');
            const ep = await bukaPeranti(dev);
            for (let i = 0; i < bait.length; i += 512) await dev.transferOut(ep, bait.slice(i, i + 512)); // cebisan: printer murah tak tercekik
        },
        binaResit,
        binaTiketDapur,
    };

    window.PosLuarTalian = LuarTalian;
    window.PosPrinter = Printer;
})();
