import test from 'node:test';
import assert from 'node:assert/strict';
import { receiptText, ThermalPrinter } from '../public/js/pos-printer.js';
const sale = { status: 'completed', sale_no: 'S-1', items: [{ product_name: 'Tea\x1b@\n', quantity: 2, unit_price: '3.00', line_total: '7.00', modifiers: [{ name: 'Milk', price: 1 }] }], subtotal: 7, discount_amount: 1, tax_amount: .36, service_charge_amount: .6, total_amount: 6.96, amount_paid: 10, change_amount: 3.04, tenders: [{ method: 'cash', amount: 6.96 }] };
function fixture(result) {
    const calls = [];
    const device = {
        opened: false,
        configuration: { interfaces: [{ interfaceNumber: 2, alternates: [{ interfaceClass: 7, alternateSetting: 1, endpoints: [{ direction: 'out', type: 'bulk', endpointNumber: 3 }] }] }] },
        async open() { this.opened = true; },
        async claimInterface(n) { calls.push(['claim', n]); },
        async selectAlternateInterface(n, alt) { calls.push(['alt', n, alt]); },
        async releaseInterface() {}, async close() { this.opened = false; },
        async transferOut(n, data) { calls.push(['out', n, data]); return result ?? { status: 'ok', bytesWritten: data.length }; },
    };
    const usb = { addEventListener() {}, async requestDevice(options) { calls.push(['permission', options]); return device; } };
    return { printer: new ThermalPrinter(usb, true), calls, device };
}
test('server totals, modifiers, split tender and control-byte sanitization', () => {
    const text = receiptText(sale);
    assert.match(text, /JUMLAH: RM 6.96/);
    assert.match(text, /Baki: RM 3.04/);
    assert.match(text, /Milk RM 1.00/);
    assert.match(text, /cash: RM 6.96/);
    assert.ok(!text.includes('\x1b'));
    assert.ok(text.split('\n').every(line => line.length <= 32));
    assert.throws(() => receiptText({ ...sale, status: 'draft' }));
    assert.throws(() => receiptText(sale, 80));
});
test('unsupported contexts do not request permissions', async () => {
    const printer = new ThermalPrinter({ addEventListener() {}, requestDevice() { throw Error('must not call'); } }, false);
    assert.equal(printer.supported, false);
    await assert.rejects(printer.connect(), /HTTPS/);
});
test('explicit connection discovers interface and endpoint; disconnect closes', async () => {
    const { printer, calls, device } = fixture();
    assert.equal(calls.length, 0);
    await printer.connect();
    assert.deepEqual(calls[0], ['permission', { filters: [{ classCode: 7 }] }]);
    await printer.print(sale);
    assert.deepEqual(calls[1], ['claim', 2]);
    assert.deepEqual(calls[2], ['alt', 2, 1]);
    assert.equal(calls[3][1], 3);
    assert.deepEqual([...calls[3][2].slice(0, 2)], [27, 64]);
    await printer.disconnect();
    assert.equal(device.opened, false);
});
test('partial writes never retry and invalidate connection', async () => {
    const { printer, calls } = fixture({ status: 'ok', bytesWritten: 1 });
    await printer.connect();
    await assert.rejects(printer.print(sale), /mungkin separa/);
    assert.equal(printer.device, null);
    assert.equal(calls.filter(c => c[0] === 'out').length, 1);
});
test('unsupported interfaces cleaned up and permission cancellation handled', async () => {
    const { printer, device } = fixture();
    device.configuration.interfaces[0].alternates[0].interfaceClass = 255;
    await assert.rejects(printer.connect(), /antara muka/);
    assert.equal(device.opened, false);
    assert.equal(printer.busy, false);
    const cancelled = new ThermalPrinter({ addEventListener() {}, async requestDevice() { throw Error('cancelled'); } }, true);
    await assert.rejects(cancelled.connect(), /cancelled/);
    assert.equal(cancelled.busy, false);
});
test('disconnected and concurrent jobs rejected', async () => {
    const { printer } = fixture();
    await assert.rejects(printer.print(sale), /Sambungkan/);
    await printer.connect();
    printer.busy = true;
    await assert.rejects(printer.print(sale), /digunakan/);
});
