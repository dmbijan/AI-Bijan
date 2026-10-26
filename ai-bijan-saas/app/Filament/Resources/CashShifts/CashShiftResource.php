<?php

namespace App\Filament\Resources\CashShifts;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\POS\Models\CashShift;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource POS — Syif Tunai (baca sahaja).
 *
 * ⚠️ Syif dibuka/ditutup melalui `CashShiftService` — kiraan wang sebenar.
 */
class CashShiftResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'pos';
    }

    protected static ?string $model = CashShift::class;

    protected static ?string $slug = 'pos-syif';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Jualan';

    protected static ?string $navigationLabel = 'Syif Tunai';

    protected static ?string $modelLabel = 'Syif Tunai';

    protected static ?string $pluralModelLabel = 'Syif Tunai';

    protected static ?int $navigationSort = 22;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('register.name')->label('Kaunter')->placeholder('—'),
                Tables\Columns\TextColumn::make('shift_date')->label('Tarikh')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('opener.name')->label('Dibuka Oleh')->placeholder('—'),
                Tables\Columns\TextColumn::make('opening_cash')->label('Tunai Awal')->money('MYR')->alignEnd(),
                Tables\Columns\TextColumn::make('expected_cash')->label('Jangkaan')->money('MYR')->alignEnd()->toggleable(),
                Tables\Columns\TextColumn::make('closing_cash')->label('Tunai Akhir')->money('MYR')->alignEnd()->placeholder('—'),
                Tables\Columns\TextColumn::make('cash_discrepancy')
                    ->label('Beza')
                    ->money('MYR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        (float) $state < 0 => 'danger',
                        (float) $state > 0 => 'warning',
                        default => 'success',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'open' => 'success',
                        'closed' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->defaultSort('shift_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(['open' => 'Terbuka', 'closed' => 'Ditutup']),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashShifts::route('/'),
            'view' => Pages\ViewCashShift::route('/{record}'),
        ];
    }
}