@php
    /** @var \App\Modules\Invoicing\Models\Invoice $invoice */
    $currency = $invoice->currency ?: 'MYR';
    $money = fn ($v) => $currency . ' ' . number_format((float) $v, 2);
    $seller = config('invoicing.einvois');
    $sellerName = config('invoicing.seller.name', config('app.name', 'AI Bijan'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #1a1a1a; margin: 0; }
        .wrap { padding: 32px 36px; }
        h1 { font-size: 24px; margin: 0; letter-spacing: 1px; color: #0f172a; }
        .muted { color: #64748b; }
        .right { text-align: right; }
        .row::after { content: ""; display: table; clear: both; }
        .col-left { float: left; width: 55%; }
        .col-right { float: right; width: 45%; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.items th { background: #0f172a; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .totals { width: 42%; float: right; margin-top: 14px; }
        .totals td { padding: 5px 10px; }
        .totals .grand td { border-top: 2px solid #0f172a; font-weight: bold; font-size: 14px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 2px; }
        .section-title { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin: 0 0 4px; }
        .footer { margin-top: 36px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="row">
        <div class="col-left">
            <h1>INVOICE</h1>
            <div class="muted" style="margin-top:6px;font-weight:bold;color:#0f172a;">{{ $sellerName }}</div>
            @if (!empty($seller['seller_address']))<div class="muted">{{ $seller['seller_address'] }} {{ $seller['seller_postal_code'] ?? '' }}</div>@endif
            @if (!empty($seller['seller_phone']))<div class="muted">Tel: {{ $seller['seller_phone'] }}</div>@endif
            @if (!empty($seller['seller_email']))<div class="muted">{{ $seller['seller_email'] }}</div>@endif
            @if (!empty($seller['seller_registration_no']))<div class="muted">Reg. No: {{ $seller['seller_registration_no'] }}</div>@endif
            @if (!empty($seller['seller_ein']))<div class="muted">TIN: {{ $seller['seller_ein'] }}</div>@endif
        </div>
        <div class="col-right right">
            <div class="label">Invoice No.</div>
            <div style="font-size:16px;font-weight:bold;">{{ $invoice->invoice_number }}</div>
            <div style="margin-top:8px;">
                <span class="label">Issue date:</span> {{ optional($invoice->issue_date)->format('d/m/Y') }}<br>
                <span class="label">Due date:</span> {{ optional($invoice->due_date)->format('d/m/Y') }}
            </div>
            <div style="margin-top:8px;">
                @php
                    $statusColor = match ($invoice->status) {
                        'paid' => '#16a34a', 'overdue' => '#dc2626', 'cancelled' => '#64748b', default => '#2563eb',
                    };
                @endphp
                <span class="badge" style="background: {{ $statusColor }}; color:#fff;">{{ $invoice->status }}</span>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top:24px;">
        <div class="col-left">
            <div class="box">
                <div class="section-title">Bill to</div>
                <div style="font-weight:bold;">{{ $invoice->customer_name ?: optional($invoice->customer)->name }}</div>
                @if ($invoice->customer_email)<div class="muted">{{ $invoice->customer_email }}</div>@endif
                @if ($invoice->customer_tax_id)<div class="muted">Tax ID: {{ $invoice->customer_tax_id }}</div>@endif
                @if ($invoice->billing_address)<div class="muted">{{ $invoice->billing_address }}</div>@endif
            </div>
        </div>
        <div class="col-right">
            @if ($invoice->einvois_reference_no)
                <div class="box">
                    <div class="section-title">e-Invois (LHDN)</div>
                    <div class="muted">Status: {{ $invoice->einvois_submission_status }}</div>
                    <div class="muted">UUID: {{ $invoice->einvois_reference_no }}</div>
                </div>
            @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Description</th>
                <th class="num" style="width:50px;">Qty</th>
                <th class="num" style="width:90px;">Unit price</th>
                <th class="num" style="width:70px;">Disc.</th>
                <th class="num" style="width:70px;">Tax</th>
                <th class="num" style="width:100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if ($item->product_sku)<br><span class="muted">SKU: {{ $item->product_sku }}</span>@endif
                        @if ($item->description)<br><span class="muted">{{ $item->description }}</span>@endif
                    </td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                    <td class="num">{{ $money($item->unit_price) }}</td>
                    <td class="num">{{ $item->discount_amount > 0 ? $money($item->discount_amount) : '-' }}</td>
                    <td class="num">{{ $item->tax_amount > 0 ? $money($item->tax_amount) : '-' }}</td>
                    <td class="num">{{ $money($item->line_total) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted" style="text-align:center;padding:18px;">No line items.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ $money($invoice->subtotal) }}</td></tr>
        @if ($invoice->discount_total > 0)<tr><td>Discount</td><td class="right">- {{ $money($invoice->discount_total) }}</td></tr>@endif
        @if ($invoice->tax_total > 0)<tr><td>Tax (SST)</td><td class="right">{{ $money($invoice->tax_total) }}</td></tr>@endif
        @if ($invoice->shipping_total > 0)<tr><td>Shipping</td><td class="right">{{ $money($invoice->shipping_total) }}</td></tr>@endif
        <tr class="grand"><td>Total ({{ $currency }})</td><td class="right">{{ $money($invoice->grand_total) }}</td></tr>
        @if ($invoice->amount_paid > 0)
            <tr><td>Paid</td><td class="right">- {{ $money($invoice->amount_paid) }}</td></tr>
            <tr class="grand"><td>Balance due</td><td class="right">{{ $money($invoice->amount_outstanding) }}</td></tr>
        @endif
    </table>
    <div style="clear:both;"></div>

    @if ($invoice->notes || $invoice->terms_conditions)
        <div style="margin-top:28px;">
            @if ($invoice->notes)<div class="section-title">Notes</div><div class="muted" style="margin-bottom:10px;">{{ $invoice->notes }}</div>@endif
            @if ($invoice->terms_conditions)<div class="section-title">Terms &amp; conditions</div><div class="muted">{{ $invoice->terms_conditions }}</div>@endif
        </div>
    @endif

    <div class="footer">
        This is a computer-generated invoice. {{ $sellerName }}
    </div>
</div>
</body>
</html>
