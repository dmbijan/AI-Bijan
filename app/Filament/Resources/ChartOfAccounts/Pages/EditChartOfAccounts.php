<?php

namespace App\Filament\Resources\ChartOfAccounts\Pages;

use App\Filament\Resources\ChartOfAccounts\ChartOfAccountResource;
use Filament\Resources\Pages\EditRecord;

class EditChartOfAccounts extends EditRecord
{
    protected static string $resource = ChartOfAccountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}