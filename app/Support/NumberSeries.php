<?php

namespace App\Support;

use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/** Nombor siri per tenant/jenis/tahun, dikunci baris (tiada nombor berganda walaupun serentak). */
class NumberSeries
{
    public static function next(string $type, string $prefix): string
    {
        return DB::transaction(function () use ($type, $prefix) {
            $tid = app(TenantContext::class)->id();
            $y = (int) date('Y');
            DB::table('document_sequences')->insertOrIgnore(['tenant_id' => $tid, 'type' => $type, 'year' => $y, 'last' => 0]);
            $s = DB::table('document_sequences')->where(['tenant_id' => $tid, 'type' => $type, 'year' => $y])->lockForUpdate()->first();
            DB::table('document_sequences')->where('id', $s->id)->update(['last' => $s->last + 1]);

            return sprintf('%s-%d-%04d', $prefix, $y, $s->last + 1);
        });
    }
}
