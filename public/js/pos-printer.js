export function receiptText(sale, columns = 32) {
    if (![32, 48].includes(columns)) throw new Error('Lebar resit tidak sah.');
    if (sale.status !== 'completed' || !Array.isArray(sale.items) || !sale.items.length) throw new Error('Resit memerlukan jualan selesai dengan item.');
    // ESC/POS text must never contain commands supplied through product names or notes.
    const safe = value => String(value ?? '').replace(/[^\x20-\x7e]/g, '?');
    const money = value => {
        const n = Number(value ?? 0);
        if (!Number.isFinite(n)) throw new Error('Amaun resit tidak sah.');
        return 'RM ' + n.toFixed(2);
    };
    const lines = [];
    const line = value => {
        const text = safe(value);
        for (let i = 0; i < text.length; i += columns) lines.push(text.slice(i, i + columns));
    };
    line('RESIT POS');
    line(sale.sale_no);
    line(sale.completed_at ?? sale.sale_date);
    line('Kaunter: ' + (sale.register?.name ?? '-'));
    line('Juruwang: ' + (sale.cashier?.name ?? '-'));
    line('-'.repeat(columns));
    for (const item of sale.items) {
        line(item.product_name);
        line(`${item.quantity} x ${money(item.unit_price)} = ${money(item.line_total)}`);
        for (const modifier of item.modifiers ?? []) line('+ ' + (modifier.name ?? modifier.option_name ?? '-') + ' ' + money(modifier.price ?? modifier.price_adjustment));
    }
    line('-'.repeat(columns));
    for (const [label, key] of [['Subtotal', 'subtotal'], ['Diskaun', 'discount_amount'], ['Cukai', 'tax_amount'], ['Caj perkhidmatan', 'service_charge_amount'], ['JUMLAH', 'total_amount'], ['Dibayar', 'amount_paid'], ['Baki', 'change_amount']]) line(label + ': ' + money(sale[key]));
    for (const tender of sale.tenders ?? []) line((tender.method ?? tender.payment_method ?? 'Bayaran') + ': ' + money(tender.amount));
    if (!sale.tenders?.length) line('Bayaran: ' + (sale.payment_method ?? '-'));
    if (sale.notes) line(sale.notes);
    line('Terima kasih');
    return lines.join('\n') + '\n\n\n';
}

export class ThermalPrinter {
    constructor(usb = globalThis.navigator?.usb, secure = globalThis.isSecureContext) {
        this.usb = usb;
        this.secure = secure;
        this.device = null;
        this.busy = false;
        usb?.addEventListener('disconnect', event => {
            if (event.device === this.device) this.device = null;
        });
    }
    get supported() { return Boolean(this.secure && this.usb); }
    async connect() {
        if (!this.supported) throw new Error('WebUSB memerlukan Chrome/Edge desktop melalui HTTPS atau localhost.');
        if (this.busy) throw new Error('Pencetak sedang digunakan.');
        this.busy = true;
        try {
            // Called directly by the connect button: never request USB permission on load or payment.
            const device = await this.usb.requestDevice({ filters: [{ classCode: 7 }] });
            await this.disconnect();
            this.device = device;
            await device.open();
            if (!device.configuration) await device.selectConfiguration(device.configurations[0]?.configurationValue ?? 1);
            const choices = device.configuration.interfaces.flatMap(iface => iface.alternates.map(alt => ({ iface, alt, endpoint: alt.endpoints.find(e => e.direction === 'out' && e.type === 'bulk') }))).filter(c => c.alt.interfaceClass === 7 && c.endpoint);
            if (choices.length !== 1) throw new Error('Perlu satu antara muka pencetak USB bulk OUT yang jelas.');
            const { iface, alt, endpoint } = choices[0];
            this.interfaceNumber = iface.interfaceNumber;
            await device.claimInterface(this.interfaceNumber);
            await device.selectAlternateInterface(this.interfaceNumber, alt.alternateSetting);
            this.endpoint = endpoint.endpointNumber;
        } catch (error) {
            await this.disconnect();
            throw error;
        } finally { this.busy = false; }
    }
    async disconnect() {
        const device = this.device;
        this.device = null;
        if (device?.opened) {
            try { await device.releaseInterface(this.interfaceNumber); } catch {}
            try { await device.close(); } catch {}
        }
    }
    async print(sale, columns = 32) {
        if (this.busy) throw new Error('Pencetak sedang digunakan.');
        if (!this.device?.opened) throw new Error('Sambungkan pencetak dahulu.');
        const bytes = new TextEncoder().encode('\x1b@' + receiptText(sale, columns));
        this.busy = true;
        try {
            for (let offset = 0; offset < bytes.length; offset += 4096) {
                const chunk = bytes.slice(offset, offset + 4096);
                const result = await this.device.transferOut(this.endpoint, chunk);
                if (result.status !== 'ok' || result.bytesWritten !== chunk.length) throw new Error('Pemindahan USB tidak lengkap.');
            }
        } catch (error) {
            await this.disconnect();
            throw new Error('Cetakan mungkin separa. Semak kertas sebelum cuba lagi. ' + error.message);
        } finally { this.busy = false; }
    }
}
