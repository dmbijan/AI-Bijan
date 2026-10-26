<?php

namespace App\Filament\Resources\FinanceEntries\Pages;

use App\Filament\Resources\FinanceEntries\FinanceEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinanceEntries extends CreateRecord
{
    protected static string $resource = FinanceEntryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}