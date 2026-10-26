<?php

/**
 * Pulihkan 3 resource POS yang ditulis secara manual (tidak dijana skrip).
 * Jalankan selepas `jana-resource.php` jika folder Resources dijana semula.
 *
 * ⚠️ Skrip ini DEFENSIF: tidak menimpa jika fail sudah wujud.
 */

$asas = __DIR__.'/../app/Filament/Resources';

$posSales = <<<'PHP'
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
PHP;

$posSalesList = <<<'PHP'
<?php

namespace App\Filament\Resources\PosSales\Pages;

use App\Filament\Resources\PosSales\PosSaleResource;
use Filament\Resources\Pages\ListRecords;

class ListPosSales extends ListRecords
{
    protected static string $resource = PosSaleResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — jualan POS hanya melalui `SaleService`.
        return [];
    }
}
PHP;

$posSalesView = <<<'PHP'
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
PHP;

$posRegister = <<<'PHP'
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
PHP;

$posRegisterList = <<<'PHP'
<?php

namespace App\Filament\Resources\PosRegisters\Pages;

use App\Filament\Resources\PosRegisters\PosRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPosRegisters extends ListRecords
{
    protected static string $resource = PosRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
PHP;

$posRegisterCreate = <<<'PHP'
<?php

namespace App\Filament\Resources\PosRegisters\Pages;

use App\Filament\Resources\PosRegisters\PosRegisterResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosRegister extends CreateRecord
{
    protected static string $resource = PosRegisterResource::class;

    /**
     * ⚠️ `pos_registers.code` dan `created_by` ialah NOT NULL.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'POS-'.strtoupper(substr(md5(uniqid('', true)), 0, 6));
        }

        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
PHP;

$posRegisterEdit = <<<'PHP'
<?php

namespace App\Filament\Resources\PosRegisters\Pages;

use App\Filament\Resources\PosRegisters\PosRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPosRegister extends EditRecord
{
    protected static string $resource = PosRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
PHP;

$cashShift = <<<'PHP'
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
PHP;

$cashShiftList = <<<'PHP'
<?php

namespace App\Filament\Resources\CashShifts\Pages;

use App\Filament\Resources\CashShifts\CashShiftResource;
use Filament\Resources\Pages\ListRecords;

class ListCashShifts extends ListRecords
{
    protected static string $resource = CashShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
PHP;

$cashShiftView = <<<'PHP'
<?php

namespace App\Filament\Resources\CashShifts\Pages;

use App\Filament\Resources\CashShifts\CashShiftResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCashShift extends ViewRecord
{
    protected static string $resource = CashShiftResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Maklumat Syif')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('register.name')->label('Kaunter')->placeholder('—'),
                    Infolists\Components\TextEntry::make('sale_date')->label('Tarikh')->date('d/m/Y')->placeholder('—'),
                    Infolists\Components\TextEntry::make('shift_date')->label('Tarikh Syif')->date('d/m/Y'),
                ]),
        ]);
    }
}
PHP;

$fail = [
    'PosSales/PosSaleResource.php' => $posSales,
    'PosSales/Pages/ListPosSales.php' => $posSalesList,
    'PosSales/Pages/ViewPosSale.php' => $posSalesView,
    'PosRegisters/PosRegisterResource.php' => $posRegister,
    'PosRegisters/Pages/ListPosRegisters.php' => $posRegisterList,
    'PosRegisters/Pages/CreatePosRegister.php' => $posRegisterCreate,
    'PosRegisters/Pages/EditPosRegister.php' => $posRegisterEdit,
    'CashShifts/CashShiftResource.php' => $cashShift,
    'CashShifts/Pages/ListCashShifts.php' => $cashShiftList,
    'CashShifts/Pages/ViewCashShift.php' => $cashShiftView,
];

$n = 0;

foreach ($fail as $laluan => $kandungan) {
    $penuh = $asas.'/'.$laluan;
    @mkdir(dirname($penuh), 0777, true);

    if (! file_exists($penuh)) {
        file_put_contents($penuh, $kandungan);
        $n++;
    }
}

echo "Resource POS dipulihkan: {$n}".PHP_EOL;
