<?php

namespace Tests\Feature;

use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
    }

    private function buatInvois(): Invoice
    {
        [, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga Sari');
        $this->dalamTenant($t);

        $invoice = Invoice::create([
            'tenant_id' => $t->id,
            'customer_name' => 'Pelanggan Jauhari',
            'customer_email' => 'jauhari@contoh.my',
            'customer_tax_id' => 'C1234567890',
            'billing_address' => 'No 1, Jalan Mawar, 50000 Kuala Lumpur',
            'issue_date' => '2026-10-29',
            'due_date' => '2026-11-28',
            'subtotal' => 100.00,
            'tax_total' => 8.00,
            'grand_total' => 108.00,
            'amount_paid' => 0.00,
            'status' => 'issued',
            'currency' => 'MYR',
        ]);

        // Item dipasang dalam memori; ujian ini menyemak pemaparan PDF, bukan storan item.
        $invoice->setRelation('items', collect([
            new InvoiceItem([
                'product_name' => 'Jambangan Ros',
                'product_sku' => 'FLW-001',
                'quantity' => 2,
                'unit_price' => 50.00,
                'tax_percentage' => 8.00,
                'tax_amount' => 8.00,
                'line_total' => 108.00,
            ]),
        ]));

        return $invoice;
    }

    #[Test]
    public function pdf_invois_dijana_sebagai_pdf_sebenar(): void
    {
        $invoice = $this->buatInvois();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoicing::pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4');

        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF', $output, 'Output sepatutnya fail PDF sebenar.');
    }

    #[Test]
    public function template_memaparkan_nombor_invois_dan_jumlah(): void
    {
        $invoice = $this->buatInvois();

        $html = view('invoicing::pdf.invoice', ['invoice' => $invoice])->render();

        $this->assertStringContainsString($invoice->invoice_number, $html);
        $this->assertStringContainsString('MYR 108.00', $html);
        $this->assertStringContainsString('Jambangan Ros', $html);
        $this->assertStringContainsString('Pelanggan Jauhari', $html);
    }
}
