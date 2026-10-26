<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Support\Tenancy\TenantContext;

// 1. Tanpa konteks
app(TenantContext::class)->clear();
echo 'TANPA KONTEKS:', PHP_EOL;
echo '  canViewAny = '.var_export(ExpenseResource::canViewAny(), true).PHP_EOL;
echo '  canCreate  = '.var_export(ExpenseResource::canCreate(), true).PHP_EOL;
echo '  nav        = '.var_export(ExpenseResource::shouldRegisterNavigation(), true).PHP_EOL;

// 2. Semakan kaedah mana yang dipanggil
$r = new ReflectionMethod(ExpenseResource::class, 'canCreate');
echo PHP_EOL.'canCreate dinyatakan dalam: '.$r->getDeclaringClass()->getName().PHP_EOL;

$r2 = new ReflectionMethod(ExpenseResource::class, 'canViewAny');
echo 'canViewAny dinyatakan dalam: '.$r2->getDeclaringClass()->getName().PHP_EOL;
