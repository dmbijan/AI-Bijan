<?php

namespace App\Filament\Widgets;

use App\Modules\Invoicing\Models\Invoice;
use App\Support\Tenancy\TenantContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Invois tertunggak — belum dibayar penuh, tertua dahulu.
 *
 * ⚠️ Isih ikut `issue_date` MENAIK (tertua dahulu) supaya pengguna nampak
 * hutang yang paling perlu dikejar. Widget KPI guna jumlah, widget ini
 * guna senarai — dua perspektif, satu sumber.
 */
class InvoisTertunggakWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    /** Nyahaktifkan lazy loading — lihat nota dalam `HasilTrendChart`. */
    protected static bool $isLazy = false;

    protected static ?string $heading = 'Invois Tertunggak';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->where('tenant_id', (int) app(TenantContext::class)->id())
                    ->where('amount_outstanding', '>', 0)
                    ->whereIn('status', ['issued', 'partial'])
                    ->orderBy('issue_date')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invois')
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->placeholder('—')
                    ->limit(28),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Tarikh Terbit')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Jumlah')
                    ->money('MYR')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('amount_outstanding')
                    ->label('Tertunggak')
                    ->money('MYR')
                    ->alignEnd()
                    ->weight('bold')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('umur')
                    ->label('Umur')
                    ->state(fn (Invoice $r) => $r->issue_date
                        ? (int) $r->issue_date->diffInDays(now()).' hari'
                        : '—')
                    ->badge()
                    ->color(fn (string $state) => (int) $state >= 30 ? 'danger' : 'warning'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tiada invois tertunggak')
            ->emptyStateDescription('Semua invois telah dibayar. Syabas!')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function canView(): bool
    {
        $ctx = app(TenantContext::class);

        return $ctx->has() && $ctx->can('invoicing.view');
    }
}
