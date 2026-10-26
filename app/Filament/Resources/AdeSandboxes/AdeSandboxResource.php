<?php

namespace App\Filament\Resources\AdeSandboxes;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\ADE\Models\AdeSandbox;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource Filament — ADE Sandbox (A2 Worktree Isolation).
 *
 * Ruang kerja terasing bagi setiap subtugas — tiada dua ejen berebut fail.
 */
class AdeSandboxResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'ade';
    }

    protected static ?string $model = AdeSandbox::class;

    protected static ?string $slug = 'ade-sandbox';

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'ADE';

    protected static ?string $navigationLabel = 'Sandbox ADE';

    protected static ?string $modelLabel = 'Sandbox';

    protected static ?string $pluralModelLabel = 'Sandbox ADE';

    protected static ?int $navigationSort = 91;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Jenis')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('base_branch')->label('Branch Asas'),
                Tables\Columns\TextColumn::make('branch_name')->label('Branch')->toggleable(),
                Tables\Columns\IconColumn::make('baseline')->label('Baseline')
                    ->boolean(fn ($record) => $record->adaBaseline()),
                Tables\Columns\TextColumn::make('created_at')->label('Dicipta')->dateTime()->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(150),
            Forms\Components\Select::make('type')->label('Jenis')->options([
                'worktree' => 'Git Worktree',
                'directory' => 'Direktori',
                'container' => 'Container',
                'none' => 'Tiada Pengasingan',
            ])->default('worktree')->required(),
            Forms\Components\TextInput::make('base_branch')->label('Branch Asas')->default('main')->maxLength(100),
            Forms\Components\TextInput::make('repo_path')->label('Laluan Repositori')->maxLength(500)
                ->helperText('Hanya untuk jenis worktree.'),
            Forms\Components\Textarea::make('error_message')->label('Ralat')->rows(2)->columnSpanFull()
                ->disabled()->dehydrated(false),
        ])->columns(2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdeSandboxes::route('/'),
            'create' => Pages\CreateAdeSandbox::route('/create'),
            'view' => Pages\ViewAdeSandbox::route('/{record}'),
            'edit' => Pages\EditAdeSandbox::route('/{record}/edit'),
        ];
    }
}
