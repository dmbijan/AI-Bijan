<?php

namespace App\Filament\Resources\BiCompetitors;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\BusinessIntel\Models\BiCompetitor;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource Filament — Pesaing.
 *
 * Dijana oleh `skrip/jana-resource.php`. Boleh diperkaya dengan borang
 * terperinci tanpa mengubah kontrak kebenaran.
 */
class BiCompetitorResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'business_intel';
    }

    protected static ?string $model = BiCompetitor::class;

    protected static ?string $slug = 'pesaing';

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'Intelijen';

    protected static ?string $navigationLabel = 'Pesaing';

    protected static ?string $modelLabel = 'Pesaing';

    protected static ?string $pluralModelLabel = 'Pesaing';

    protected static ?int $navigationSort = 31;

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::kolum())
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * Borang generik — medan teks/nombor/tarikh yang biasa.
     *
     * ⚠️ Hanya medan yang BENAR-BENAR wujud pada jadual dimasukkan, supaya
     * resource tidak pecah apabila skema berubah. Ganti dengan borang
     * eksplisit bila keperluan domain jelas.
     */
    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        $model = new (static::$model);
        $lajur = \Illuminate\Support\Facades\Schema::getColumnListing($model->getTable());

        // Jangan dedah medan sistem.
        $haram = ['id', 'tenant_id', 'created_at', 'updated_at', 'deleted_at', 'ulid'];

        $komponen = [];

        foreach ($lajur as $l) {
            if (in_array($l, $haram, true)) {
                continue;
            }

            $label = ucfirst(str_replace('_', ' ', $l));

            $komponen[] = match (true) {
                $l === 'notes', $l === 'description' => \Filament\Forms\Components\Textarea::make($l)->label($label)->rows(2)->columnSpanFull(),
                str_ends_with($l, '_at'), str_ends_with($l, '_date'), $l === 'date' => \Filament\Forms\Components\DateTimePicker::make($l)->label($label),
                str_starts_with($l, 'is_'), str_starts_with($l, 'has_') => \Filament\Forms\Components\Toggle::make($l)->label($label),
                str_contains($l, 'amount'), str_contains($l, 'total'), str_contains($l, 'price') => \Filament\Forms\Components\TextInput::make($l)->label($label)->numeric()->prefix('RM'),
                default => \Filament\Forms\Components\TextInput::make($l)->label($label)->maxLength(255),
            };
        }

        return $form->schema($komponen)->columns(2);
    }

    /**
     * Kolum generik: ambil 4-6 medan teks pertama yang ada pada jadual.
     *
     * ⚠️ Dibina secara dinamik supaya resource ini tidak pecah apabila
     * skema berubah. Ganti dengan kolum eksplisit bila borang diperkaya.
     *
     * @return array<int, Tables\Columns\Column>
     */
    private static function kolum(): array
    {
        $model = new (static::$model);
        $kolum = [];

        // ⚠️ Urutan PENTING — `array_slice(0, 6)` hanya mengambil 6 pertama.
        // Nombor rujukan (expense_no/invoice_number/order_no/...) mesti
        // mendahului medan hiasan supaya ia sentiasa kelihatan.
        $medan = [
            'expense_no', 'invoice_number', 'order_no', 'sale_no', 'ticket_no',
            'code', 'number', 'no',
            'name', 'title', 'subject',
            'vendor_name', 'customer_name', 'supplier_name',
            'status', 'total_amount', 'grand_total', 'total', 'amount',
            'date', 'issue_date', 'created_at',
        ];

        $lajur = \Illuminate\Support\Facades\Schema::getColumnListing($model->getTable());

        foreach ($medan as $m) {
            if (! in_array($m, $lajur, true)) {
                continue;
            }

            $kolum[] = Tables\Columns\TextColumn::make($m)
                ->label(ucfirst(str_replace('_', ' ', $m)))
                ->searchable()
                ->sortable()
                ->limit(40);
        }

        if ($kolum === []) {
            $kolum[] = Tables\Columns\TextColumn::make('id')->label('ID')->sortable();
        }

        return array_slice($kolum, 0, 6);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBiCompetitors::route('/'),
            'create' => Pages\CreateBiCompetitors::route('/create'),
            'view' => Pages\ViewBiCompetitors::route('/{record}'),
            'edit' => Pages\EditBiCompetitors::route('/{record}/edit'),
        ];
    }
}