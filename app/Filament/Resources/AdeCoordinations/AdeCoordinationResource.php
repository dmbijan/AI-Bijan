<?php

namespace App\Filament\Resources\AdeCoordinations;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\ADE\Models\AdeCoordination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource Filament — ADE Coordination (A3 Sub-Agent Coordinator).
 *
 * Orkestrasi berbilang sub-ejen serentak: implementer, tester, reviewer.
 */
class AdeCoordinationResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'ade';
    }

    protected static ?string $model = AdeCoordination::class;

    protected static ?string $slug = 'ade-coordination';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'ADE';

    protected static ?string $navigationLabel = 'Orchestrasi';

    protected static ?string $modelLabel = 'Orchestrasi';

    protected static ?string $pluralModelLabel = 'Orchestrasi ADE';

    protected static ?int $navigationSort = 92;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('task_count')->label('Tugas')->sortable(),
                Tables\Columns\TextColumn::make('completed_count')->label('Selesai')->sortable(),
                Tables\Columns\TextColumn::make('failed_count')->label('Gagal')->sortable(),
                Tables\Columns\TextColumn::make('max_parallel')->label('Paralel')->toggleable(),
                Tables\Columns\TextColumn::make('duration_ms')->label('Masa (ms)')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dicipta')->dateTime()->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(150),
            Forms\Components\Textarea::make('objective')->label('Matlamat')->rows(3)->columnSpanFull(),
            Forms\Components\TextInput::make('max_parallel')->label('Paralel Maks')->numeric()->default(4)->minValue(1)->maxValue(10),
            Forms\Components\Textarea::make('result')->label('Hasil (JSON)')
                ->rows(3)->columnSpanFull()->disabled()->dehydrated(false),
        ])->columns(2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdeCoordinations::route('/'),
            'create' => Pages\CreateAdeCoordination::route('/create'),
            'view' => Pages\ViewAdeCoordination::route('/{record}'),
            'edit' => Pages\EditAdeCoordination::route('/{record}/edit'),
        ];
    }
}
