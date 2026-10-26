<?php

namespace App\Filament\Resources\PayrollRuns\Pages;

use App\Filament\Resources\PayrollRuns\PayrollRunResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayrollRuns extends CreateRecord
{
    protected static string $resource = PayrollRunResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}