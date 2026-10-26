<?php

namespace App\Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Services\StatutoryExportService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Eksport statutori (BAB 57) — EPF, SOCSO, EIS, PCB, bank, ringkasan.
 *
 * Hanya payroll run yang diluluskan/difinalkan boleh dieksport.
 */
class PayrollExportController extends Controller
{
    public function __construct(private StatutoryExportService $svc) {}

    /** Senarai jenis eksport yang tersedia untuk run ini. */
    public function senarai(int $runId)
    {
        $run = PayrollRun::withCount('details')->findOrFail($runId);

        return response()->json([
            'run_id' => $run->id,
            'period' => $run->period_start?->format('Y-m'),
            'status' => $run->status,
            'employees' => $run->details_count,
            'boleh_eksport' => $this->bolehEksport($run),
            'jenis' => ['epf', 'socso', 'eis', 'pcb', 'bank', 'semua'],
        ]);
    }

    /** Muat turun satu jenis fail. */
    public function muatTurun(Request $r, int $runId, string $jenis)
    {
        $run = PayrollRun::findOrFail($runId);

        abort_unless($this->bolehEksport($run), 422,
            'Payroll run mesti diluluskan atau difinalkan sebelum dieksport.');

        abort_unless(in_array($jenis, ['epf', 'socso', 'eis', 'pcb', 'bank', 'semua'], true), 404);

        $fail = $this->svc->jana($run, $jenis);

        // Jika 'semua', pulangkan JSON metadata + fail pertama; klien boleh muat turun satu-satu.
        if ($jenis === 'semua') {
            app(AuditLogger::class)->log('payroll.export_all', $run, null, ['files' => array_keys($fail)]);

            return response()->json([
                'run_id' => $run->id,
                'files' => collect($fail)->map(fn ($f, $k) => [
                    'jenis' => $k,
                    'filename' => $f['filename'],
                    'rows' => $f['rows'],
                    'total' => $f['total'],
                    'checksum' => $f['checksum'],
                    'download_url' => $k === 'ringkasan'
                        ? null
                        : url("/api/v1/payroll/runs/{$run->id}/export/{$k}"),
                ])->values(),
                'ringkasan_content' => $fail['ringkasan']['content'] ?? null,
            ]);
        }

        $f = $fail[$jenis];

        app(AuditLogger::class)->log('payroll.export', $run, null, [
            'jenis' => $jenis,
            'rows' => $f['rows'],
            'checksum' => $f['checksum'],
        ]);

        return response($f['content'], Response::HTTP_OK, [
            'Content-Type' => $f['mime'],
            'Content-Disposition' => 'attachment; filename="'.$f['filename'].'"',
            'X-Checksum-Sha256' => $f['checksum'],
            'X-Row-Count' => (string) $f['rows'],
        ]);
    }

    private function bolehEksport(PayrollRun $run): bool
    {
        return in_array($run->status, ['approved', 'finalized', 'diluluskan', 'dimuktamadkan'], true);
    }
}
