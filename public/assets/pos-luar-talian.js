/* AI Bijan POS — mod luar talian.
 * Katalog & kaunter disimpan dalam peranti; bila talian putus, jualan masuk giliran dan
 * disegerak ke POST /api/v1/pos/sales/checkout bila talian pulih. `client_uuid` yang sama
 * bermakna pelayan pulangkan jualan sedia ada, jadi segerak berulang tak jadi pendua. */
(function () {
    'use strict';

    const K_KATALOG = 'aib-pos-katalog';
    const K_KAUNTER = 'aib-pos-kaunter';
    const K_GILIRAN = 'aib-pos-giliran';

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
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    window.PosLuarTalian = {
        simpanKatalog: rows => tulis(K_KATALOG, rows),
        katalog: () => baca(K_KATALOG, []),
        simpanKaunter: rows => tulis(K_KAUNTER, rows),
        kaunter: () => baca(K_KAUNTER, []),
        giliran: () => baca(K_GILIRAN, []),
        tambah(jualan) { tulis(K_GILIRAN, [...this.giliran(), jualan]); },
        buang(id) { tulis(K_GILIRAN, this.giliran().filter(j => j.payload.client_uuid !== id)); },
        kemas(id, ubah) { tulis(K_GILIRAN, this.giliran().map(j => j.payload.client_uuid === id ? { ...j, ...ubah } : j)); },
        ralatRangkaian,
        uuid,

        /** Hantar satu jualan. Pulangkan {ok, sale} atau {luarTalian: true}; ralat pelayan dilontar. */
        async hantar(payload) {
            try {
                const res = await App.api.post('/api/v1/pos/sales/checkout', payload);
                return { ok: true, sale: res.data ?? res };
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
                    const r = await this.hantar(j.payload);
                    if (r.luarTalian) break; // masih tiada talian: cuba lagi kemudian
                    this.buang(j.payload.client_uuid);
                    berjaya++;
                } catch (e) {
                    // Pelayan tolak (cth. 422): simpan ralat, jangan buang — pemilik perlu nampak.
                    this.kemas(j.payload.client_uuid, { ralat: e.message || 'Ditolak pelayan' });
                }
            }
            return berjaya;
        },
    };
})();
