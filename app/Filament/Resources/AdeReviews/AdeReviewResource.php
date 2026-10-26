<?php

namespace App\Filament\Resources\AdeReviews;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\ADE\Models\AdeReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource Filament — ADE Review (A4 Review Surface).
 *
 * Diff walkthrough + test runner + preview dalam satu permukaan.
 */
class AdeReviewResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'ade';
    }

    protected static ?string $model = AdeReview::class;

    protected static ?string $slug = 'ade-review';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'ADE';

    protected static ?string $navigationLabel = 'Semakan Kod';

    protected static ?string $modelLabel = 'Semakan';

    protected static ?string $pluralModelLabel = 'Semakan Kod ADE';

    protected static ?int $navigationSort = 93;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Tajuk')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('severity')->label('Keseriusan')->badge(),
                Tables\Columns\TextColumn::make('additions')->label('+')->sortable(),
                Tables\Columns\TextColumn::make('deletions')->label('-')->sortable(),
                Tables\Columns\IconColumn::make('test_results')->label('Ujian')
                    ->boolean(fn ($record) => $record->ujianLulus()),
                Tables\Columns\IconColumn::make('findings')->label('Kritikal')
                    ->boolean(fn ($record) => $record->adaKritikal()),
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
            Forms\Components\TextInput::make('title')->label('Tajuk')->required()->maxLength(200),
            Forms\Components\Select::make('status')->label('Status')->options([
                'open' => 'Dibuka',
                'in_review' => 'Dalam Semakan',
                'approved' => 'Diluluskan',
                'changes_requested' => 'Perubahan Diperlukan',
                'rejected' => 'Ditolak',
                'merged' => 'Digabungkan',
            ])->default('open'),
            Forms\Components\Select::make('severity')->label('Keseriusan')->options([
                'low' => 'Rendah',
                'medium' => 'Sederhana',
                'high' => 'Tinggi',
                'critical' => 'Kritikal',
            ])->default('medium'),
            Forms\Components\TextInput::make('additions')->label('Tambahan')->numeric()->default(0),
            Forms\Components\TextInput::make('deletions')->label('Penghapusan')->numeric()->default(0),
            Forms\Components\Textarea::make('summary')->label('Ringkasan')->rows(3)->columnSpanFull(),
            Forms\Components\Textarea::make('findings')->label('Penemuan (JSON)')
                ->rows(3)->columnSpanFull()->dehydrateStateUsing(fn ($s) => is_array($s) ? $s : json_decode((string) $s, true))
                ->afterStateHydrated(fn ($c) => is_array($c) ? json_encode($c, JSON_PRETTY_PRINT) : $c),
            Forms\Components\Textarea::make('test_results')->label('Hasil Ujian (JSON)')
                ->rows(2)->columnSpanFull()->dehydrateStateUsing(fn ($s) => is_array($s) ? $s : json_decode((string) $s, true))
                ->afterStateHydrated(fn ($c) => is_array($c) ? json_encode($c, JSON_PRETTY_PRINT) : $c),
        ])->columns(2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdeReviews::route('/'),
            'create' => Pages\CreateAdeReview::route('/create'),
            'view' => Pages\ViewAdeReview::route('/{record}'),
            'edit' => Pages\EditAdeReview::route('/{record}/edit'),
        ];
    }
}
