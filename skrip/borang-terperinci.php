<?php

/**
 * Tulis semula resource tertentu dengan borang terperinci.
 * Jalankan: php skrip/borang-terperinci.php
 *
 * ⚠️ HANYA menulis resource yang disenaraikan di sini. Skrip lain
 * (`jana-resource.php`) kekal menjana resource generik.
 */

$asas = __DIR__.'/../app/Filament/Resources';

$fail = [];

// =====================================================================
// PEKERJA (HRMS)
// =====================================================================
$fail['HrEmployees/HrEmployeeResource.php'] = <<<'PHP'
<?php

namespace App\Filament\Resources\HrEmployees;

use App\Filament\Concerns\HasModulePermission;
use App\Modules\HRMS\Models\Department;
use App\Modules\HRMS\Models\Designation;
use App\Modules\HRMS\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Resource Filament — Pekerja (HRMS).
 *
 * ⚠️ Medan sensitif (IC, pasport) menggunakan `password()->revealable()`
 * supaya tidak terpapar di skrin terbuka (PDPA). Ia TIDAK dipaparkan
 * dalam senarai — hanya dalam borang.
 */
class HrEmployeeResource extends Resource
{
    use HasModulePermission;

    protected static function permissionNamespace(): string
    {
        return 'hrms';
    }

    protected static ?string $model = Employee::class;

    protected static ?string $slug = 'pekerja';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Sumber Manusia';

    protected static ?string $navigationLabel = 'Pekerja';

    protected static ?string $modelLabel = 'Pekerja';

    protected static ?string $pluralModelLabel = 'Pekerja';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Maklumat Peribadi')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('employee_no')
                        ->label('No. Pekerja')
                        ->maxLength(40)
                        ->unique(ignoreRecord: true)
                        ->helperText('Kosongkan untuk jana automatik.'),

                    Forms\Components\TextInput::make('ic_number')
                        ->label('No. Kad Pengenalan')
                        ->maxLength(20)
                        ->password()
                        ->revealable()
                        ->helperText('Data sensitif (PDPA) — untuk penggajian sahaja.'),

                    Forms\Components\TextInput::make('first_name')
                        ->label('Nama Pertama')
                        ->required()
                        ->maxLength(80),

                    Forms\Components\TextInput::make('last_name')
                        ->label('Nama Akhir')
                        ->maxLength(80),

                    Forms\Components\TextInput::make('email')
                        ->label('E-mel')
                        ->email()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('phone')
                        ->label('Telefon')
                        ->tel()
                        ->maxLength(30),

                    Forms\Components\DatePicker::make('date_of_birth')
                        ->label('Tarikh Lahir')
                        ->maxDate(now()->subYears(15)),

                    Forms\Components\Select::make('gender')
                        ->label('Jantina')
                        ->options(['male' => 'Lelaki', 'female' => 'Perempuan']),

                    Forms\Components\Select::make('marital_status')
                        ->label('Status Perkahwinan')
                        ->options([
                            'single' => 'Bujang',
                            'married' => 'Berkahwin',
                            'divorced' => 'Bercerai',
                            'widowed' => 'Balu/Duda',
                        ]),

                    Forms\Components\TextInput::make('nationality')
                        ->label('Warganegara')
                        ->maxLength(60)
                        ->default('Malaysia'),
                ]),

            Forms\Components\Section::make('Alamat')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('address_line1')->label('Alamat 1')->maxLength(160)->columnSpanFull(),
                    Forms\Components\TextInput::make('address_line2')->label('Alamat 2')->maxLength(160)->columnSpanFull(),
                    Forms\Components\TextInput::make('city')->label('Bandar')->maxLength(80),
                    Forms\Components\TextInput::make('postcode')->label('Poskod')->maxLength(10),
                    Forms\Components\TextInput::make('state')->label('Negeri')->maxLength(80),
                    Forms\Components\TextInput::make('country')->label('Negara')->maxLength(80)->default('Malaysia'),
                ]),

            Forms\Components\Section::make('Penggajian')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('department_id')
                        ->label('Jabatan')
                        ->options(fn () => Department::query()->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('designation_id')
                        ->label('Jawatan')
                        ->options(fn () => Designation::query()->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('employment_type')
                        ->label('Jenis Pekerjaan')
                        ->options([
                            'permanent' => 'Tetap',
                            'contract' => 'Kontrak',
                            'part_time' => 'Sambilan',
                            'intern' => 'Latihan Industri',
                            'probation' => 'Percubaan',
                        ])
                        ->default('permanent'),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Aktif',
                            'probation' => 'Percubaan',
                            'on_leave' => 'Bercuti',
                            'suspended' => 'Digantung',
                            'resigned' => 'Berhenti',
                            'terminated' => 'Diberhentikan',
                        ])
                        ->default('active')
                        ->required(),

                    Forms\Components\DatePicker::make('date_joined')
                        ->label('Tarikh Mula Kerja')
                        ->default(now()),

                    Forms\Components\DatePicker::make('probation_end_date')
                        ->label('Tamat Percubaan'),

                    Forms\Components\DatePicker::make('contract_end_date')
                        ->label('Tamat Kontrak'),

                    Forms\Components\DatePicker::make('last_worked_date')
                        ->label('Hari Terakhir Bekerja'),
                ]),

            Forms\Components\Section::make('Hubungan Kecemasan')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('emergency_contact_name')
                        ->label('Nama Waris')
                        ->maxLength(120),

                    Forms\Components\TextInput::make('emergency_contact_phone')
                        ->label('Telefon Waris')
                        ->tel()
                        ->maxLength(30),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_no')
                    ->label('No. Pekerja')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nama')
                    ->formatStateUsing(fn ($record) => trim(($record->first_name ?? '').' '.($record->last_name ?? '')))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mel')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Jabatan')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('designation.name')
                    ->label('Jawatan')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('employment_type')
                    ->label('Jenis')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'permanent' => 'Tetap',
                        'contract' => 'Kontrak',
                        'part_time' => 'Sambilan',
                        'intern' => 'Latihan',
                        'probation' => 'Percubaan',
                        default => ucfirst((string) $state),
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'probation', 'on_leave' => 'warning',
                        'resigned', 'terminated', 'suspended' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('date_joined')
                    ->label('Mula Kerja')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('employee_no')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'probation' => 'Percubaan',
                        'on_leave' => 'Bercuti',
                        'resigned' => 'Berhenti',
                        'terminated' => 'Diberhentikan',
                    ]),

                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Jabatan')
                    ->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        // ⚠️ Nama kelas halaman menggunakan nama FOLDER (`HrEmployees`).
        return [
            'index' => Pages\ListHrEmployees::route('/'),
            'create' => Pages\CreateHrEmployees::route('/create'),
            'view' => Pages\ViewHrEmployees::route('/{record}'),
            'edit' => Pages\EditHrEmployees::route('/{record}/edit'),
        ];
    }
}
PHP;

// =====================================================================
// CARTA AKAUN (Finance)
// =====================================================================
$fail['ChartOfAccounts/ChartOfAccountResource.php'] = <<<'PHP'
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
        // ⚠️ Nama kelas halaman menggunakan nama FOLDER (`ChartOfAccounts`).
        return [
            'index' => Pages\ListChartOfAccounts::route('/'),
            'create' => Pages\CreateChartOfAccounts::route('/create'),
            'view' => Pages\ViewChartOfAccounts::route('/{record}'),
            'edit' => Pages\EditChartOfAccounts::route('/{record}/edit'),
        ];
    }
}
PHP;

$n = 0;

foreach ($fail as $laluan => $kandungan) {
    $penuh = $asas.'/'.$laluan;
    @mkdir(dirname($penuh), 0777, true);
    file_put_contents($penuh, $kandungan);
    $n++;
}

echo "Resource borang terperinci ditulis: {$n}".PHP_EOL;
