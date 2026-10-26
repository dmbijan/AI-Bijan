<?php

namespace App\Filament\Resources\AdeSkills;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\ADE\Models\AdeSkill;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource Filament — ADE Skills (A1 Skills Engine).
 *
 * Pustaka kaedah kerja berulang: brainstorming → plan → TDD → review.
 * Corak `obra/superpowers` (MIT).
 */
class AdeSkillResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'ade';
    }

    protected static ?string $model = AdeSkill::class;

    protected static ?string $slug = 'ade-skill';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'ADE';

    protected static ?string $navigationLabel = 'Skill ADE';

    protected static ?string $modelLabel = 'Skill ADE';

    protected static ?string $pluralModelLabel = 'Skill ADE';

    protected static ?int $navigationSort = 90;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('key')->label('Kunci')->searchable(),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge(),
                Tables\Columns\TextColumn::make('origin_project')->label('Sumber')->toggleable(),
                Tables\Columns\TextColumn::make('usage_count')->label('Penggunaan')->sortable(),
                Tables\Columns\TextColumn::make('success_rate')->label('Jaya %')->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')->label('Aktif')->boolean(),
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
            Forms\Components\TextInput::make('key')->label('Kunci')->required()->maxLength(80)
                ->helperText('Huruf kecil, nombor, titik, garis bawah, tanda hubung.'),
            Forms\Components\TextInput::make('category')->label('Kategori')->default('general')->maxLength(50),
            Forms\Components\Textarea::make('description')->label('Penerangan')->rows(2)->columnSpanFull(),
            Forms\Components\Textarea::make('system_prompt')->label('System Prompt')->rows(4)->columnSpanFull(),
            Forms\Components\Textarea::make('steps')->label('Langkah (JSON)')
                ->rows(4)->columnSpanFull()->dehydrateStateUsing(fn ($s) => is_array($s) ? $s : json_decode((string) $s, true))
                ->afterStateHydrated(fn ($c) => is_array($c) ? json_encode($c, JSON_PRETTY_PRINT) : $c),
            Forms\Components\Textarea::make('triggers')->label('Pencetus (JSON)')
                ->rows(2)->columnSpanFull()->dehydrateStateUsing(fn ($s) => is_array($s) ? $s : json_decode((string) $s, true))
                ->afterStateHydrated(fn ($c) => is_array($c) ? json_encode($c, JSON_PRETTY_PRINT) : $c),
            Forms\Components\Toggle::make('is_enabled')->label('Aktif')->default(true),
            Forms\Components\Toggle::make('is_public')->label('Tunjuk di Marketplace')->default(false),
        ])->columns(2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdeSkills::route('/'),
            'create' => Pages\CreateAdeSkill::route('/create'),
            'view' => Pages\ViewAdeSkill::route('/{record}'),
            'edit' => Pages\EditAdeSkill::route('/{record}/edit'),
        ];
    }
}
