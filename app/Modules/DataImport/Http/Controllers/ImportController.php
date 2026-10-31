<?php

namespace App\Modules\DataImport\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DataImport\Models\ImportJob;
use App\Modules\DataImport\Services\ImportService;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Import Pintar. Jenis datang dari laluan (setiap jenis ada pagar kebenaran sendiri). */
class ImportController extends Controller
{
    public function __construct(private ImportService $svc) {}

    public function template(): Response
    {
        $type = $this->jenis();
        return response($this->svc->templat($type), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="templat-'.$type.'.csv"',
        ]);
    }

    public function fields(): JsonResponse
    {
        $type = $this->jenis();
        return response()->json(['data' => collect(ImportService::MEDAN[$type])->map(fn ($m, $k) => ['field' => $k, 'required' => $m[1]])->values()]);
    }

    public function index(): JsonResponse
    {
        $type = $this->jenis();
        return response()->json(['data' => ImportJob::where('type', $type)->latest()->limit(50)->get()]);
    }

    public function store(Request $r): JsonResponse
    {
        $type = $this->jenis();
        $d = $r->validate([
            'file' => 'required_without:csv|file|mimes:csv,txt|max:10240',
            'csv' => 'required_without:file|string|max:10000000',
            'mapping' => 'nullable|array',
            'filename' => 'nullable|string|max:190',
        ]);
        $csv = $r->hasFile('file') ? (string) file_get_contents($r->file('file')->getRealPath()) : (string) $d['csv'];
        $nama = $r->hasFile('file') ? $r->file('file')->getClientOriginalName() : ($d['filename'] ?? null);
        try {
            $job = $this->svc->pratonton($type, $csv, $nama, $d['mapping'] ?? null, $r->user()->id);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $this->papar($job)], 201);
    }

    public function show(Request $r, int $id): JsonResponse
    {
        $type = $this->jenis();
        $job = ImportJob::where('type', $type)->findOrFail($id);

        return response()->json(['data' => $this->papar($job, $r->input('filter'))]);
    }

    public function commit(Request $r, int $id): JsonResponse
    {
        $type = $this->jenis();
        $job = ImportJob::where('type', $type)->findOrFail($id);
        try {
            $job = $this->svc->sahkan($job, $r->user()->id);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        app(AuditLogger::class)->log('import.committed', $job, null, ['type' => $type, 'rows' => $job->valid_rows] + ($job->result ?? []));

        return response()->json(['data' => $this->papar($job)]);
    }

    public function cancel(int $id): JsonResponse
    {
        $type = $this->jenis();
        $job = ImportJob::where('type', $type)->findOrFail($id);
        try {
            $job = $this->svc->batal($job);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $this->papar($job)]);
    }

    /** Jenis ditetapkan oleh laluan (defaults), bukan input pengguna. */
    private function jenis(): string
    {
        return (string) request()->route('type');
    }

    private function papar(ImportJob $job, ?string $tapis = null): array
    {
        $baris = $job->rows()->orderBy('row_no')
            ->when($tapis === 'errors', fn ($q) => $q->where('action', 'skip'))
            ->when(in_array($tapis, ['create', 'update'], true), fn ($q) => $q->where('action', $tapis))
            ->limit(500)->get(['row_no', 'data', 'errors', 'action', 'target_id']);

        return $job->only(['id', 'type', 'status', 'filename', 'mapping', 'total_rows', 'valid_rows', 'error_rows', 'result', 'committed_at']) + [
            'headers' => $job->options['headers'] ?? [],
            'summary' => [
                'create' => $job->rows()->where('action', 'create')->count(),
                'update' => $job->rows()->where('action', 'update')->count(),
                'skip' => $job->rows()->where('action', 'skip')->count(),
            ],
            'rows' => $baris,
        ];
    }
}
