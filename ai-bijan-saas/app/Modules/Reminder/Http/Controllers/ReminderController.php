<?php

namespace App\Modules\Reminder\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reminder\Models\Reminder;
use App\Modules\Reminder\Models\ReminderInbox;
use App\Modules\Reminder\Models\ReminderSchedule;
use App\Modules\Reminder\Models\ReminderType;
use App\Modules\Reminder\Services\ReminderService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __construct(private ReminderService $service) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    // ---------------------------------------------------------------
    // Katalog jenis
    // ---------------------------------------------------------------

    public function jenis(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ReminderType::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function janaKatalog(): JsonResponse
    {
        $dicipta = $this->service->janaKatalog($this->tenantId());

        return response()->json([
            'success' => true,
            'message' => "{$dicipta} jenis peringatan dicipta",
            'data' => ['dicipta' => $dicipta],
        ]);
    }

    public function simpanJenis(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+$/'],
            'name' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:60'],
            'source_module' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'default_priority' => ['nullable', 'in:low,normal,high,urgent'],
            'default_lead_hours' => ['nullable', 'integer', 'min:0', 'max:8760'],
            'requires_ai_triage' => ['nullable', 'boolean'],
            'default_channels' => ['nullable', 'array'],
        ]);

        $jenis = ReminderType::updateOrCreate(
            ['tenant_id' => $this->tenantId(), 'key' => $validated['key']],
            array_merge($validated, ['tenant_id' => $this->tenantId()])
        );

        return response()->json(['success' => true, 'data' => $jenis], 201);
    }

    // ---------------------------------------------------------------
    // Peringatan
    // ---------------------------------------------------------------

    public function index(Request $request): JsonResponse
    {
        $query = Reminder::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('due_at');

        foreach (['status', 'priority', 'type_key'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        if ($request->boolean('lewat')) {
            $query->where('due_at', '<', now())
                ->whereIn('status', [Reminder::STATUS_PENDING, Reminder::STATUS_SNOOZED]);
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type_key' => ['required', 'string', 'max:80'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'action_note' => ['nullable', 'string', 'max:1000'],
            'subject_type' => ['nullable', 'string', 'max:180'],
            'subject_id' => ['nullable', 'integer'],
            'subject_label' => ['nullable', 'string', 'max:255'],
            'subject_due_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer'],
            'assigned_role' => ['nullable', 'string', 'max:80'],
            'channels' => ['nullable', 'array'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'lead_hours' => ['nullable', 'integer', 'min:0', 'max:8760'],
            'due_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
        ]);

        $reminder = $this->service->cipta($this->tenantId(), $validated['type_key'], $validated);

        return response()->json(['success' => true, 'data' => $reminder], 201);
    }

    public function show(int $id): JsonResponse
    {
        $reminder = Reminder::query()
            ->with('deliveries')
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $reminder]);
    }

    public function hantar(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'channels' => ['nullable', 'array'],
            'channels.*' => ['in:in_app,email,whatsapp,sms,push'],
        ]);

        $hasil = $this->service->hantar($this->tenantId(), $id, $validated['channels'] ?? null);

        return response()->json(['success' => true, 'data' => $hasil]);
    }

    public function tunda(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'minutes' => ['required', 'integer', 'min:5', 'max:43200'],
        ]);

        $reminder = $this->service->tunda($this->tenantId(), $id, $validated['minutes']);

        return response()->json(['success' => true, 'data' => $reminder]);
    }

    public function sahkan(int $id): JsonResponse
    {
        $reminder = $this->service->sahkan($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $reminder]);
    }

    public function lengkapkan(int $id): JsonResponse
    {
        $reminder = $this->service->lengkapkan($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $reminder]);
    }

    public function buang(int $id): JsonResponse
    {
        $reminder = $this->service->buang($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $reminder]);
    }

    // ---------------------------------------------------------------
    // Kotak masuk
    // ---------------------------------------------------------------

    public function kotakMasuk(Request $request): JsonResponse
    {
        $hanyaBelum = $request->boolean('unread', true);

        return response()->json([
            'success' => true,
            'data' => $this->service->kotakMasuk($this->tenantId(), null, $hanyaBelum),
        ]);
    }

    public function tandakanDibaca(int $id): JsonResponse
    {
        $item = $this->service->tandakanDibaca($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $item]);
    }

    // ---------------------------------------------------------------
    // Jadwal
    // ---------------------------------------------------------------

    public function jadual(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ReminderSchedule::query()
                ->where('tenant_id', $this->tenantId())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function simpanJadual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type_key' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:180'],
            'frequency' => ['required', 'in:once,daily,weekly,monthly,quarterly,annually'],
            'time_of_day' => ['nullable', 'date_format:H:i'],
            'interval_count' => ['nullable', 'integer', 'min:1', 'max:365'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'channels' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $jadual = ReminderSchedule::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
            'next_run_at' => now(),
        ]));

        return response()->json(['success' => true, 'data' => $jadual], 201);
    }

    public function jalankanJadual(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->jalankanJadual($this->tenantId()),
        ]);
    }

    // ---------------------------------------------------------------
    // Ringkasan & eskalasi
    // ---------------------------------------------------------------

    public function ringkasan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->ringkasan($this->tenantId()),
        ]);
    }

    public function eskalasi(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->eskalasiLewat($this->tenantId()),
        ]);
    }
}
