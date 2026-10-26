<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Modules\Entitlement\Services\EntitlementService;
use App\Support\Tenancy\TenantContext;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

/**
 * Tetapan Syarikat — profil, langganan & pengguna.
 *
 * ⚠️ Prinsip keselamatan:
 *  - Hanya pengguna dengan `tenant.settings.view` boleh LIHAT halaman ini.
 *  - Hanya `tenant.settings.update` boleh SIMPAN perubahan.
 *  - `plan_id` TIDAK boleh diubah dari sini — naik taraf plan mesti melalui
 *    aliran bil (elak tenant menaikkan dirinya sendiri ke Enterprise).
 */
class TetapanSyarikat extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Tetapan';

    protected static ?string $navigationLabel = 'Tetapan Syarikat';

    protected static ?string $title = 'Tetapan Syarikat';

    protected static ?string $slug = 'tetapan';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.tetapan-syarikat';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $ctx = app(TenantContext::class);

        return $ctx->has() && $ctx->can('tenant.settings.view');
    }

    public function mount(): void
    {
        $tenant = $this->tenant();

        $this->form->fill([
            'name' => $tenant->name,
            'timezone' => $tenant->timezone ?? 'Asia/Kuala_Lumpur',
            'currency' => $tenant->currency ?? 'MYR',
            'locale' => $tenant->locale ?? 'ms',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Maklumat Syarikat')
                    ->description('Butiran asas yang muncul pada invois dan dokumen.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Syarikat')
                            ->required()
                            ->maxLength(120)
                            ->disabled(fn () => ! $this->bolehUbah())
                            ->columnSpanFull(),

                        Forms\Components\Select::make('timezone')
                            ->label('Zon Masa')
                            ->options([
                                'Asia/Kuala_Lumpur' => 'Malaysia (GMT+8)',
                                'Asia/Singapore' => 'Singapura (GMT+8)',
                                'Asia/Jakarta' => 'Indonesia (GMT+7)',
                                'Asia/Bangkok' => 'Thailand (GMT+7)',
                                'Asia/Manila' => 'Filipina (GMT+8)',
                            ])
                            ->required()
                            ->disabled(fn () => ! $this->bolehUbah()),

                        Forms\Components\Select::make('currency')
                            ->label('Mata Wang')
                            ->options([
                                'MYR' => 'Ringgit Malaysia (MYR)',
                                'SGD' => 'Dolar Singapura (SGD)',
                                'IDR' => 'Rupiah Indonesia (IDR)',
                                'THB' => 'Baht Thailand (THB)',
                                'PHP' => 'Peso Filipina (PHP)',
                                'USD' => 'Dolar Amerika (USD)',
                            ])
                            ->required()
                            ->disabled(fn () => ! $this->bolehUbah()),

                        Forms\Components\Select::make('locale')
                            ->label('Bahasa')
                            ->options([
                                'ms' => 'Bahasa Melayu',
                                'en' => 'English',
                            ])
                            ->required()
                            ->disabled(fn () => ! $this->bolehUbah()),
                    ]),
            ])
            ->statePath('data');
    }

    public function simpan(): void
    {
        if (! $this->bolehUbah()) {
            Notification::make()
                ->title('Tiada kebenaran')
                ->body('Anda tidak dibenarkan mengubah tetapan syarikat.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        $tenant = $this->tenant();
        $tenant->update([
            'name' => $data['name'],
            'timezone' => $data['timezone'],
            'currency' => $data['currency'],
            'locale' => $data['locale'],
        ]);

        Notification::make()
            ->title('Tetapan disimpan')
            ->success()
            ->send();
    }

    /**
     * Ringkasan langganan untuk paparan.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(): array
    {
        return app(EntitlementService::class)->ringkasan((int) $this->tenant()->id);
    }

    /** Senarai pengguna dalam tenant semasa. */
    public function pengguna(): array
    {
        return TenantUser::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenant()->id)
            ->with(['user:id,name,email,is_active'])
            ->get()
            ->map(fn (TenantUser $m) => [
                'nama' => $m->user?->name ?? '—',
                'emel' => $m->user?->email ?? '—',
                'status' => $m->status,
                'peranan' => $m->roles()->pluck('name')->implode(', ') ?: 'Tiada peranan',
            ])
            ->all();
    }

    /** Semua plan aktif (untuk paparan perbandingan — bukan untuk tukar). */
    public function senaraiPlan(): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('price_monthly')
            ->get(['code', 'name', 'price_monthly', 'max_users'])
            ->map(fn (Plan $p) => [
                'kod' => $p->code,
                'nama' => $p->name,
                'harga' => (float) $p->price_monthly,
                'pengguna' => $p->max_users,
            ])
            ->all();
    }

    private function tenant(): Tenant
    {
        return app(TenantContext::class)->tenant();
    }

    /** Awam — dipanggil daripada Blade untuk keadaan butang simpan. */
    public function bolehUbah(): bool
    {
        return app(TenantContext::class)->can('tenant.settings.update');
    }
}
