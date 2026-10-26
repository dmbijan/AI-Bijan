@extends('layouts.app')

@php
    $names = [
        'pejabat'       => 'Pejabat Maya',
        'pos'           => 'POS',
        'crm'           => 'CRM',
        'invoicing'     => 'Invois',
        'marketplace'   => 'Marketplace',
        'booking'       => 'B-Booking',
        'omnichannel'   => 'Omnichannel',
        'helpdesk'      => 'Helpdesk',
        'loyalty'       => 'Kesetiaan',
        'affiliate'     => 'Affiliate',
        'link'          => 'B-Link',
        'marketing'     => 'Kempen',
        'site'          => 'B-Site',
        'inventory'     => 'Inventori',
        'cogs'          => 'COGS',
        'manufacturing' => 'Perkilangan',
        'logistics'     => 'Logistik',
        'scm'           => 'SCM',
        'assets'        => 'Aset',
        'finance'       => 'Kewangan',
        'payroll'       => 'Payroll',
        'hrms'          => 'HRMS',
        'projects'      => 'Projek',
        'sign-docs'     => 'Sign & Docs',
        'analytics'     => 'Analitik',
        'brainhub'      => 'Brain Hub',
        'industry'      => 'Industry Packs',
    ];
    $label = $names[$module] ?? ucfirst($module);
@endphp

@section('title', $label)

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ $label }}</h1>
        <p class="page-desc">Modul ini tersedia melalui API tetapi antara muka belum dibina.</p>
    </div>
</div>

<div class="card">
    <div class="empty">
        <div class="empty-icon" aria-hidden="true">▤</div>
        <div class="empty-title">Antara muka belum tersedia</div>
        <p class="empty-text">
            Backend {{ $label }} sudah berfungsi sepenuhnya melalui REST API.
            Antara muka akan ditambah pada fasa seterusnya.
        </p>
        <div class="row mt-4" style="justify-content:center">
            <a href="/app" class="btn btn-sm">Kembali ke Papan Pemuka</a>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <div>
            <h2 class="card-title">Endpoint API</h2>
            <p class="card-subtitle">Boleh diuji dari konsol atau Postman</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Kaedah</th><th>Laluan</th></tr>
            </thead>
            <tbody>
                <tr><td><span class="badge badge-info">GET</span></td><td class="mono">/api/v1/{{ $module }}</td></tr>
                <tr><td><span class="badge badge-success">POST</span></td><td class="mono">/api/v1/{{ $module }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
