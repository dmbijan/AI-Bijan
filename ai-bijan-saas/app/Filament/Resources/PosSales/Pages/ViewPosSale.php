<?php

namespace App\Filament\Resources\PosSales\Pages;

use App\Filament\Resources\PosSales\PosSaleResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPosSale extends ViewRecord
{
    protected static string $resource = PosSaleResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Maklumat Jualan')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('sale_no')->label('No. Jualan'),
                    Infolists\Components\TextEntry::make('register.name')->label('Kaunter')->placeholder('—'),
                    Infolists\Components\TextEntry::make('sale_date')->label('Tarikh')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('status')->label('Status')->badge(),
                    Infolists\Components\TextEntry::make('completed_at')->label('Selesai Pada')->dateTime('d/m/Y H:i')->placeholder('—'),
                ]),

            Infolists\Components\Section::make('Jumlah')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('subtotal')->label('Subtotal')->money('MYR'),
                    Infolists\Components\TextEntry::make('discount_amount')->label('Diskaun')->money('MYR'),
                    Infolists\Components\TextEntry::make('tax_amount')->label('Cukai')->money('MYR'),
                    Infolists\Components\TextEntry::make('total_amount')->label('Jumlah Keseluruhan')->money('MYR')->weight('bold'),
                    Infolists\Components\TextEntry::make('amount_paid')->label('Dibayar')->money('MYR'),
                    Infolists\Components\TextEntry::make('change_amount')->label('Baki Wang')->money('MYR'),
                ]),
        ]);
    }
}