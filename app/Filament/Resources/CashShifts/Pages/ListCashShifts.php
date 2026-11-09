<?php

namespace App\Filament\Resources\CashShifts\Pages;

use App\Filament\Resources\CashShifts\CashShiftResource;
use Filament\Resources\Pages\ListRecords;

class ListCashShifts extends ListRecords
{
    protected static string $resource = CashShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
