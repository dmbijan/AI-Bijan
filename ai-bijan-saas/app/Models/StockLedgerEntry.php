<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StockLedgerEntry extends Model
{
    use BelongsToTenant;

    protected $table = 'stock_ledger';

    public $timestamps = false;

    protected $fillable = ['item_id', 'warehouse_id', 'batch_id', 'qty', 'unit_cost', 'value', 'balance_qty', 'balance_value', 'source_type', 'source_id', 'reason', 'user_id', 'posted_at'];

    protected $casts = ['posted_at' => 'datetime'];

    protected static function booted(): void
    {
        // Lejar stok tambah sahaja: pembetulan melalui pergerakan pelarasan, bukan ubah/padam.
        static::updating(fn () => throw new \LogicException('Lejar stok tidak boleh diubah.'));
        static::deleting(fn () => throw new \LogicException('Lejar stok tidak boleh dipadam.'));
    }
}
