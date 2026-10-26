<?php

namespace App\Filament\Resources\CashShifts\Pages;

use App\Filament\Resources\CashShifts\CashShiftResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCashShift extends ViewRecord
{
    protected static string $resource = CashShiftResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Maklumat Syif')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('register.name')->label('Kaunter')->placeholder('—'),
                    Infolists\Components\TextEntry::make('sale_date')->label('Tarikh')->date('d/m/Y')->placeholder('—'),
                    Infolists\Components\TextEntry::make('shift_date')->label('Tarikh Syif')->date('d/m/Y'),
                ]),
        ]);
    }
}