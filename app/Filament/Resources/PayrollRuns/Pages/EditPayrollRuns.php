<?php

namespace App\Filament\Resources\PayrollRuns\Pages;

use App\Filament\Resources\PayrollRuns\PayrollRunResource;
use Filament\Resources\Pages\EditRecord;

class EditPayrollRuns extends EditRecord
{
    protected static string $resource = PayrollRunResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}