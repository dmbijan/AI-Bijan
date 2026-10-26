<?php

namespace App\Filament\Resources\PosRegisters;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\POS\Models\POSRegister;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource POS — Kaunter (konfigurasi, boleh dicipta).
 */
class PosRegisterResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'pos';
    }

    protected static ?string $model = POSRegister::class;

    protected static ?string $slug = 'pos-kaunter';

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationGroup = 'Jualan';

    protected static ?string $navigationLabel = 'Kaunter POS';

    protected static ?string $modelLabel = 'Kaunter';

    protected static ?string $pluralModelLabel = 'Kaunter POS';

    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Maklumat Kaunter')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Kaunter')
                        ->required()
                        ->maxLength(120),

                    Forms\Components\TextInput::make('code')
                        ->label('Kod')
                        ->maxLength(40)
                        ->unique(ignoreRecord: true)
                        ->helperText('Kosongkan untuk jana automatik.'),

                    Forms\Components\TextInput::make('location')
                        ->label('Lokasi')
                        ->maxLength(160),

                    Forms\Components\Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('code')->label('Kod')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('location')->label('Lokasi')->placeholder('—'),
                Tables\Columns\TextColumn::make('sales_count')->label('Jualan')->counts('sales')->alignEnd(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosRegisters::route('/'),
            'create' => Pages\CreatePosRegister::route('/create'),
            'edit' => Pages\EditPosRegister::route('/{record}/edit'),
        ];
    }
}