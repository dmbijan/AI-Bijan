<?php

namespace App\Filament\Widgets;

use App\Modules\Orders\Models\UnifiedOrder;
use App\Support\Tenancy\TenantContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Pesanan terkini — 8 rekod terbaharu.
 *
 * Widget jadual memakai query tenant-scoped; kami TIDAK menyalin data.
 */
class PesananTerkiniWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    /** Nyahaktifkan lazy loading — lihat nota dalam `HasilTrendChart`. */
    protected static bool $isLazy = false;
    protected static ?string $heading = 'Pesanan Terkini';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                UnifiedOrder::query()
                    ->where(
                        'tenant_id',
                        (int) app(TenantContext::class)->id()
                    )
                    ->latest('created_at')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_no')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->placeholder('—')
                    ->limit(28),

                Tables\Columns\TextColumn::make('source')
                    ->label('Saluran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pos' => 'POS',
                        'marketplace' => 'Marketplace',
                        'site' => 'Laman Web',
                        'booking' => 'Tempahan',
                        'cod' => 'COD',
                        'wholesale' => 'Borong',
                        default => ucfirst((string) $state),
                    })
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid', 'delivered' => 'success',
                        'cancelled', 'refunded', 'failed' => 'danger',
                        'shipped', 'out_for_delivery' => 'info',
                        'on_hold' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Jumlah')
                    ->money('MYR')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tarikh')
                    ->since()
                    ->alignEnd(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tiada pesanan lagi')
            ->emptyStateDescription('Pesanan daripada POS, laman web atau marketplace akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }

    public static function canView(): bool
    {
        $ctx = app(TenantContext::class);

        return $ctx->has() && $ctx->can('omnichannel.view');
    }
}