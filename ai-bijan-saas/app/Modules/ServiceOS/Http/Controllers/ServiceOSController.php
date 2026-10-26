<?php

namespace App\Modules\ServiceOS\Http\Controllers;

use App\Modules\ServiceOS\Models\ServiceClient;
use App\Modules\ServiceOS\Models\ServiceContract;
use App\Modules\ServiceOS\Models\ServiceCrew;
use App\Modules\ServiceOS\Models\ServiceDelivery;
use App\Modules\ServiceOS\Models\ServiceDomainEvent;
use App\Modules\ServiceOS\Models\ServiceEquipment;
use App\Modules\ServiceOS\Models\ServicePackage;
use App\Modules\ServiceOS\Models\ServiceProject;
use App\Modules\ServiceOS\Models\ServiceQuotation;
use App\Modules\ServiceOS\Models\ServiceSession;
use App\Modules\ServiceOS\Models\ServiceTask;
use App\Modules\ServiceOS\Services\ServiceOSService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API B-ServiceOS (BAB 87 P1).
 *
 * Peraturan laluan: semua laluan statik DIDAHULUKAN sebelum `/{id}`
 * supaya `summary`, `industries`, `templates` tidak ditangkap oleh `/{id}`.
 */
class ServiceOSController extends Controller
{
    public function __construct(private ServiceOSService $servis)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    // ---------------------------------------------------------------
    // RINGKASAN + KATALOG (STATIK — mesti dahulu)
    // ---------------------------------------------------------------

    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->ringkasan($this->tenantId()),
        ]);
    }

    public function industries(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->senaraiIndustri(),
        ]);
    }

    public function checklistTemplate(Request $request): JsonResponse
    {
        $industri = $request->query('industry', 'umum');
        $templat = ServiceOSService::TEMPLAT_CHECKLIST[$industri]
            ?? ServiceOSService::TEMPLAT_CHECKLIST['umum'];

        return response()->json([
            'success' => true,
            'industry' => $industri,
            'data' => $templat,
        ]);
    }

    public function packageCatalogue(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->katalogPakej($this->tenantId()),
        ]);
    }

    public function overdueTasks(Request $request): JsonResponse
    {
        $projectId = $request->query('project_id');

        return response()->json([
            'success' => true,
            'data' => $this->servis->tugasLewat(
                $this->tenantId(),
                $projectId !== null ? (int) $projectId : null
            ),
        ]);
    }

    // ---------------------------------------------------------------
    // PELANGGAN
    // ---------------------------------------------------------------

    public function clients(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ServiceClient::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('display_name')
                ->get(),
        ]);
    }

    public function createClient(Request $request): JsonResponse
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:180'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'source' => ['nullable', 'string', 'max:60'],
            'tier' => ['nullable', Rule::in(ServiceClient::TIERS)],
            'customer_id' => ['nullable', 'integer'],
            'tags' => ['nullable', 'array'],
        ]);

        $client = $this->servis->ciptaPelanggan($this->tenantId(), $data);

        return response()->json(['success' => true, 'data' => $client], 201);
    }

    public function showClient(int $id): JsonResponse
    {
        $client = ServiceClient::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $client,
            'projects' => $client->projects()->get(),
        ]);
    }

    // ---------------------------------------------------------------
    // PAKEJ
    // ---------------------------------------------------------------

    public function packages(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ServicePackage::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createPackage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'buffer_minutes' => ['nullable', 'integer', 'min:0'],
            'inclusions' => ['nullable', 'array'],
            'exclusions' => ['nullable', 'array'],
            'requires_deposit' => ['nullable', 'boolean'],
            'deposit_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $pakej = $this->servis->ciptaPakej($this->tenantId(), $data);

        return response()->json(['success' => true, 'data' => $pakej], 201);
    }

    // ---------------------------------------------------------------
    // PROJEK
    // ---------------------------------------------------------------

    public function projects(Request $request): JsonResponse
    {
        $query = ServiceProject::query()->where('tenant_id', $this->tenantId());

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($clientId = $request->query('client_id')) {
            $query->where('client_id', (int) $clientId);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->get(),
        ]);
    }

    public function createProject(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'client_id' => ['nullable', 'integer'],
            'package_id' => ['nullable', 'integer'],
            'industry' => ['nullable', 'string', 'max:80'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'delivery_date' => ['nullable', 'date'],
            'venue' => ['nullable', 'string', 'max:200'],
            'venue_address' => ['nullable', 'string'],
            'guest_count' => ['nullable', 'integer', 'min:0'],
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', Rule::in(ServiceProject::PRIORITIES)],
        ]);

        $projek = $this->servis->ciptaProjek($this->tenantId(), $data);

        return response()->json(['success' => true, 'data' => $projek], 201);
    }

    public function showProject(int $id): JsonResponse
    {
        $projek = ServiceProject::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $projek,
            'client' => $projek->client,
            'sessions' => $projek->sessions()->get(),
            'quotations' => $projek->quotations()->get(),
            'contracts' => $projek->contracts()->get(),
            'assignments' => $projek->assignments()->get(),
            'tasks' => $projek->tasks()->get(),
            'deliveries' => $projek->deliveries()->get(),
        ]);
    }

    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(ServiceProject::ALIRAN))],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $projek = $this->servis->tukarStatus(
                $this->tenantId(),
                $id,
                $data['status'],
                $data['reason'] ?? null
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true, 'data' => $projek]);
    }

    public function projectConflicts(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->semakKonflik($this->tenantId(), $id),
        ]);
    }

    public function projectMargin(int $id): JsonResponse
    {
        $projek = $this->servis->kiraMargin($this->tenantId(), $id);

        return response()->json([
            'success' => true,
            'data' => [
                'project_id' => $projek->id,
                'contract_value' => (float) $projek->contract_value,
                'actual_cost' => (float) $projek->actual_cost,
                'margin_amount' => (float) $projek->margin_amount,
                'margin_percent' => (float) $projek->margin_percent,
            ],
        ]);
    }

    // ---------------------------------------------------------------
    // SESI
    // ---------------------------------------------------------------

    public function createSession(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'session_type' => ['nullable', Rule::in(ServiceSession::TYPES)],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'venue' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string'],
        ]);

        $sesi = $this->servis->ciptaSesi($this->tenantId(), $id, $data);

        return response()->json(['success' => true, 'data' => $sesi], 201);
    }

    public function sessionConflicts(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->konflikKruSesi($this->tenantId(), $id),
        ]);
    }

    // ---------------------------------------------------------------
    // SEBUT HARGA
    // ---------------------------------------------------------------

    public function createQuotation(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.description' => ['required', 'string'],
            'line_items.*.qty' => ['required', 'numeric', 'min:0'],
            'line_items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'valid_until' => ['nullable', 'date'],
        ]);

        $sebutHarga = $this->servis->ciptaSebutHarga($this->tenantId(), $id, $data);

        return response()->json(['success' => true, 'data' => $sebutHarga], 201);
    }

    public function acceptQuotation(Request $request, int $id): JsonResponse
    {
        $penerima = $request->validate([
            'name' => ['nullable', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:180'],
            'ip' => ['nullable', 'string', 'max:45'],
        ]);

        $kontrak = $this->servis->terimaSebutHarga($this->tenantId(), $id, $penerima);

        return response()->json([
            'success' => true,
            'data' => $kontrak,
            'message' => 'Sebut harga diterima, kontrak dicipta.',
        ]);
    }

    public function rejectQuotation(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $sebutHarga = $this->servis->tolakSebutHarga(
            $this->tenantId(),
            $id,
            $data['reason'] ?? null
        );

        return response()->json(['success' => true, 'data' => $sebutHarga]);
    }

    // ---------------------------------------------------------------
    // KRU
    // ---------------------------------------------------------------

    public function crews(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ServiceCrew::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createCrew(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'role' => ['nullable', Rule::in(ServiceCrew::ROLES)],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:180'],
            'day_rate' => ['nullable', 'numeric', 'min:0'],
            'hour_rate' => ['nullable', 'numeric', 'min:0'],
            'skills' => ['nullable', 'array'],
            'is_freelance' => ['nullable', 'boolean'],
        ]);

        $kru = $this->servis->ciptaKru($this->tenantId(), $data);

        return response()->json(['success' => true, 'data' => $kru], 201);
    }

    public function assignCrew(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'crew_id' => ['required', 'integer'],
            'service_session_id' => ['nullable', 'integer'],
            'role' => ['nullable', 'string', 'max:60'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date'],
            'agreed_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $penugasan = $this->servis->tugsakanKru(
                $this->tenantId(),
                $id,
                (int) $data['crew_id'],
                $data
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true, 'data' => $penugasan], 201);
    }

    public function suggestCrew(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->cadangKru(
                $this->tenantId(),
                $id,
                $request->query('skill'),
                (int) $request->query('limit', 5)
            ),
        ]);
    }

    // ---------------------------------------------------------------
    // TUGAS
    // ---------------------------------------------------------------

    public function createTask(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'phase' => ['nullable', Rule::in(ServiceTask::PHASES)],
            'service_session_id' => ['nullable', 'integer'],
            'assignee_crew_id' => ['nullable', 'integer'],
            'due_at' => ['nullable', 'date'],
            'is_mandatory' => ['nullable', 'boolean'],
        ]);

        $tugas = $this->servis->ciptaTugas($this->tenantId(), $id, $data);

        return response()->json(['success' => true, 'data' => $tugas], 201);
    }

    public function generateChecklist(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'industry' => ['nullable', 'string', 'max:80'],
        ]);

        $tugas = $this->servis->janaChecklist(
            $this->tenantId(),
            $id,
            $data['industry'] ?? 'umum'
        );

        return response()->json([
            'success' => true,
            'count' => count($tugas),
            'data' => $tugas,
        ], 201);
    }

    public function completeTask(int $id): JsonResponse
    {
        $tugas = $this->servis->siapkanTugas($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $tugas]);
    }

    // ---------------------------------------------------------------
    // PERALATAN
    // ---------------------------------------------------------------

    public function equipments(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ServiceEquipment::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createEquipment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'quantity_total' => ['nullable', 'integer', 'min:1'],
            'replacement_value' => ['nullable', 'numeric', 'min:0'],
            'requires_maintenance' => ['nullable', 'boolean'],
        ]);

        $peralatan = $this->servis->ciptaPeralatan($this->tenantId(), $data);

        return response()->json(['success' => true, 'data' => $peralatan], 201);
    }

    public function reserveEquipment(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'service_equipment_id' => ['required', 'integer'],
            'service_session_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'reserved_from' => ['required', 'date'],
            'reserved_to' => ['required', 'date', 'after:reserved_from'],
        ]);

        try {
            $tempahan = $this->servis->tempahPeralatan(
                $this->tenantId(),
                $id,
                (int) $data['service_equipment_id'],
                $data
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true, 'data' => $tempahan], 201);
    }

    // ---------------------------------------------------------------
    // PENYERAHAN
    // ---------------------------------------------------------------

    public function createDelivery(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'delivery_type' => ['nullable', Rule::in(ServiceDelivery::TYPES)],
            'items' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
        ]);

        $penyerahan = $this->servis->ciptaPenyerahan($this->tenantId(), $id, $data);

        return response()->json(['success' => true, 'data' => $penyerahan], 201);
    }

    public function acceptDelivery(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'received_by_name' => ['nullable', 'string', 'max:180'],
        ]);

        $penyerahan = $this->servis->terimaPenyerahan(
            $this->tenantId(),
            $id,
            $data['received_by_name'] ?? null
        );

        return response()->json(['success' => true, 'data' => $penyerahan]);
    }

    // ---------------------------------------------------------------
    // PERISTIWA DOMAIN
    // ---------------------------------------------------------------

    public function events(Request $request): JsonResponse
    {
        $query = ServiceDomainEvent::query()->where('tenant_id', $this->tenantId());

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('id')->limit(200)->get(),
        ]);
    }

    public function dispatchOutbox(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->prosesOutbox($this->tenantId()),
        ]);
    }
}
