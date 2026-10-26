<?php

namespace App\Filament\Resources\PosSales;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\POS\Models\POSSale;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resource POS — Jualan Kaunter (baca sahaja + lihat).
 *
 * ⚠️ Jualan POS dicipta melalui `SaleService` (transaksi tunai, tender,
 * inventori) — BUKAN melalui borang Filament. Membenarkan "Cipta" di sini
 * akan memintas kawalan stok dan pengiraan tukar wang.
 */
class PosSaleResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'pos';
    }

    protected static ?string $model = POSSale::class;

    protected static ?string $slug = 'pos-jualan';

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Jualan';

    protected static ?string $navigationLabel = 'Jualan POS';

    protected static ?string $modelLabel = 'Jualan POS';

    protected static ?string $pluralModelLabel = 'Jualan POS';

    protected static ?int $navigationSort = 20;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_no')
                    ->label('No. Jualan')
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('register.name')
                    ->label('Kaunter')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Tarikh')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Jumlah')
                    ->money('MYR')
                    ->alignEnd()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed', 'paid' => 'success',
                        'void', 'cancelled', 'refunded' => 'danger',
                        'draft' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->defaultSort('sale_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draf',
                        'completed' => 'Selesai',
                        'void' => 'Batal',
                        'refunded' => 'Bayar Balik',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosSales::route('/'),
            'view' => Pages\ViewPosSale::route('/{record}'),
        ];
    }
}