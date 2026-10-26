<?php

namespace App\Modules\Finance\Models;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Baris jurnal — JADUAL ANAK (tiada kolum `tenant_id`, diwarisi daripada induk).
 *
 * ⚠️ PEPIJAT DITEMUI 26 Okt 2026: model ini TIADA sebarang skop tenant.
 * `JournalEntryLine::where(...)` boleh membaca baris jurnal SEMUA tenant.
 * Test `FinanceTenantIsolationTest` mengesannya.
 *
 * Pembetulan: skop global melalui `journal_entry_id IN (...)` bagi jurnal
 * milik tenant semasa. Tambah/guna model ini melalui relasi induk apabila
 * mungkin; skop ini ialah jaring keselamatan.
 *
 * ⚠️ Konvensyen: jadual anak TIDAK menyimpan tenant_id. Jangan tambah kolum
 * itu tanpa migration + backfill. Skop induk lebih tepat dan tidak menduplikasi.
 */
class JournalEntryLine extends Model
{
    use HasFactory;

    protected $table = 'journal_entry_lines';

    protected static function booted(): void
    {
        // Skop ikut tenant induk. Tanpa tenant → tiada baris.
        static::addGlobalScope('tenant_via_induk', function ($q) {
            $ctx = app(TenantContext::class);
            $tenantId = $ctx->has() ? (int) $ctx->id() : 0;

            $q->whereIn(
                'journal_entry_lines.journal_entry_id',
                JournalEntry::withoutGlobalScope('tenant_via_induk')
                    ->select('id')
                    ->where('tenant_id', $tenantId)
            );
        });
    }

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'description',
        'currency',
        'exchange_rate',
        'amount_foreign',
        'cost_center',
        'project',
        'line_number',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'amount_foreign' => 'decimal:2',
    ];

    /**
     * Account this line refers to
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Parent journal entry
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
