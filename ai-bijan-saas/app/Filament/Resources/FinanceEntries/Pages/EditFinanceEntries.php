<?php

namespace App\Filament\Resources\FinanceEntries\Pages;

use App\Filament\Resources\FinanceEntries\FinanceEntryResource;
use Filament\Resources\Pages\EditRecord;

class EditFinanceEntries extends EditRecord
{
    protected static string $resource = FinanceEntryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}