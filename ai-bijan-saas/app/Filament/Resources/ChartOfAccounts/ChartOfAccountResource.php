<?php

namespace App\Filament\Resources\ChartOfAccounts;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\Finance\Models\ChartOfAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource Filament — Carta Akaun (COA).
 *
 * ⚠️ Akaun sistem (`is_system = true`) TIDAK boleh dipadam — ia dirujuk
 * oleh jurnal sedia ada. Filament menghalang melalui `canDelete()`.
 */
class ChartOfAccountResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'finance';
    }

    protected static ?string $model = ChartOfAccount::class;

    protected static ?string $slug = 'carta-akaun';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Kewangan';

    protected static ?string $navigationLabel = 'Carta Akaun';

    protected static ?string $modelLabel = 'Akaun';

    protected static ?string $pluralModelLabel = 'Carta Akaun';

    protected static ?int $navigationSort = 31;

    /** Akaun sistem tidak boleh dipadam. */
    public static function canDelete($record): bool
    {
        return ! (bool) $record->is_system && parent::canDelete($record);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Maklumat Akaun')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Kod Akaun')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true)
                        ->helperText('Contoh: 1000 (Aset), 4000 (Hasil), 6000 (Belanja).'),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama Akaun')
                        ->required()
                        ->maxLength(160),

                    Forms\Components\TextInput::make('name_bm')
                        ->label('Nama (BM)')
                        ->maxLength(160),

                    Forms\Components\Select::make('type')
                        ->label('Jenis')
                        ->required()
                        ->options([
                            'asset' => 'Aset',
                            'liability' => 'Liabiliti',
                            'equity' => 'Ekuiti',
                            'revenue' => 'Hasil',
                            'expense' => 'Belanja',
                        ]),

                    Forms\Components\TextInput::make('subtype')
                        ->label('Sub-jenis')
                        ->maxLength(60)
                        ->helperText('Contoh: current_asset, operating_expense.'),

                    Forms\Components\Select::make('normal_balance')
                        ->label('Baki Normal')
                        ->options(['debit' => 'Debit', 'credit' => 'Kredit'])
                        ->helperText('Aset/Belanja = Debit; Liabiliti/Ekuiti/Hasil = Kredit.'),

                    Forms\Components\Select::make('parent_id')
                        ->label('Akaun Induk')
                        ->options(fn (?ChartOfAccount $record) => ChartOfAccount::query()
                            ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Susunan')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Kawalan')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->helperText('Akaun tidak aktif tidak boleh dipilih dalam transaksi baharu.'),

                    Forms\Components\Toggle::make('allow_posting')
                        ->label('Benarkan Posting')
                        ->default(true)
                        ->helperText('Matikan untuk akaun ringkasan sahaja.'),

                    Forms\Components\Toggle::make('is_summary')
                        ->label('Akaun Ringkasan')
                        ->default(false)
                        ->helperText('Ringkasan induk — tiada posting terus.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kod')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'asset' => 'Aset',
                        'liability' => 'Liabiliti',
                        'equity' => 'Ekuiti',
                        'revenue' => 'Hasil',
                        'expense' => 'Belanja',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'asset' => 'info',
                        'liability' => 'danger',
                        'equity' => 'warning',
                        'revenue' => 'success',
                        'expense' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('normal_balance')
                    ->label('Baki')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state === 'debit' ? 'Debit' : 'Kredit'),

                Tables\Columns\IconColumn::make('allow_posting')
                    ->label('Posting')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Induk')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('code')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'asset' => 'Aset',
                        'liability' => 'Liabiliti',
                        'equity' => 'Ekuiti',
                        'revenue' => 'Hasil',
                        'expense' => 'Belanja',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif')
                    ->trueLabel('Aktif sahaja')
                    ->falseLabel('Tidak aktif sahaja'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        // ⚠️ Nama kelas halaman menggunakan nama FOLDER (`ChartOfAccounts`,
        // dengan "s"), bukan nama model. Jangan tukar tanpa menamakan
        // semula fail dalam `Pages/`.
        return [
            'index' => Pages\ListChartOfAccounts::route('/'),
            'create' => Pages\CreateChartOfAccounts::route('/create'),
            'view' => Pages\ViewChartOfAccounts::route('/{record}'),
            'edit' => Pages\EditChartOfAccounts::route('/{record}/edit'),
        ];
    }
}