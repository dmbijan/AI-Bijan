<?php

namespace App\Modules\ServiceOS\Services;

use App\Modules\ServiceOS\Models\ServiceAssignment;
use App\Modules\ServiceOS\Models\ServiceClient;
use App\Modules\ServiceOS\Models\ServiceContract;
use App\Modules\ServiceOS\Models\ServiceCrew;
use App\Modules\ServiceOS\Models\ServiceDelivery;
use App\Modules\ServiceOS\Models\ServiceDomainEvent;
use App\Modules\ServiceOS\Models\ServiceEquipment;
use App\Modules\ServiceOS\Models\ServiceEquipmentReservation;
use App\Modules\ServiceOS\Models\ServicePackage;
use App\Modules\ServiceOS\Models\ServiceProject;
use App\Modules\ServiceOS\Models\ServiceQuotation;
use App\Modules\ServiceOS\Models\ServiceSession;
use App\Modules\ServiceOS\Models\ServiceTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ServiceOSService — enjin operasi perniagaan perkhidmatan (BAB 87 P1).
 *
 * CIRI KESELAMATAN WAJIB (BAB 87.7):
 *  1. Tiada operasi wang — pembayaran kekal di Invoicing/Finance.
 *  2. Semua peralihan status disahkan melalui `ALIRAN`.
 *  3. Setiap peristiwa domain direkod dalam outbox + kunci idempotensi.
 *  4. Konflik jadual dikesan sebelum penugasan disimpan.
 *  5. Tiada penciptaan pendua pelanggan/invois (guna semula domain sedia ada).
 */
class ServiceOSService
{
    /** Had selamat untuk pencegahan gelung tak terhingga. */
    public const MAX_SESSIONS_PER_PROJECT = 200;
    public const MAX_TASKS_PER_PROJECT = 500;
    public const MAX_OUTBOX_BATCH = 100;

    // ---------------------------------------------------------------
    // PELANGGAN
    // ---------------------------------------------------------------

    /**
     * Cipta pelanggan perkhidmatan (idempoten mengikut emel).
     */
    public function ciptaPelanggan(int $tenantId, array $data): ServiceClient
    {
        $email = isset($data['email']) ? mb_strtolower(trim((string) $data['email'])) : null;

        $sedia = $email
            ? ServiceClient::query()->where('tenant_id', $tenantId)->where('email', $email)->first()
            : null;

        if ($sedia) {
            return $sedia;
        }

        return ServiceClient::create(array_merge([
            'tenant_id' => $tenantId,
            'tier' => ServiceClient::TIER_REGULAR,
            'is_active' => true,
            'country' => 'MY',
        ], $data, [
            'tenant_id' => $tenantId,
            'email' => $email,
        ]));
    }

    /**
     * Kemas kini nilai seumur hidup pelanggan daripada projek selesai.
     */
    public function kiraSemulaNilaiPelanggan(int $tenantId, int $clientId): ServiceClient
    {
        $client = ServiceClient::query()->where('tenant_id', $tenantId)->findOrFail($clientId);

        $projek = ServiceProject::query()
            ->where('tenant_id', $tenantId)
            ->where('client_id', $clientId)
            ->get();

        $selesai = $projek->where('status', ServiceProject::STATUS_COMPLETED);

        $client->update([
            'lifetime_value' => round($selesai->sum(fn ($p) => (float) $p->contract_value), 2),
            'total_projects' => $projek->count(),
        ]);

        return $client->fresh();
    }

    // ---------------------------------------------------------------
    // PAKEJ
    // ---------------------------------------------------------------

    public function ciptaPakej(int $tenantId, array $data): ServicePackage
    {
        return ServicePackage::create(array_merge([
            'tenant_id' => $tenantId,
            'currency' => 'MYR',
            'base_price' => 0,
            'deposit_percent' => 30,
            'requires_deposit' => true,
            'is_active' => true,
        ], $data, ['tenant_id' => $tenantId]));
    }

    /**
     * Katalog pakej aktif mengikut kategori.
     */
    public function katalogPakej(int $tenantId): array
    {
        return ServicePackage::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($p) => $p->category ?: 'umum')
            ->map(fn ($g) => $g->map(fn ($p) => [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'base_price' => (float) $p->base_price,
                'deposit' => $p->kiraDeposit(),
                'duration_minutes' => $p->jumlahDurasi(),
            ])->values()->all())
            ->toArray();
    }

    // ---------------------------------------------------------------
    // PROJEK
    // ---------------------------------------------------------------

    /**
     * Cipta projek perkhidmatan baharu.
     *
     * @throws \RuntimeException jika pelanggan tiada dalam tenant
     */
    public function ciptaProjek(int $tenantId, array $data): ServiceProject
    {
        if (! empty($data['client_id'])) {
            ServiceClient::query()
                ->where('tenant_id', $tenantId)
                ->findOrFail($data['client_id']);
        }

        $projek = ServiceProject::create(array_merge([
            'tenant_id' => $tenantId,
            'status' => ServiceProject::STATUS_DRAFT,
            'priority' => ServiceProject::PRIORITY_NORMAL,
            'currency' => 'MYR',
            'contract_value' => 0,
            'estimated_cost' => 0,
            'actual_cost' => 0,
            'progress_percent' => 0,
        ], $data, [
            'tenant_id' => $tenantId,
            'project_no' => $this->janaNoProjek($tenantId),
        ]));

        $this->rekodPeristiwa($tenantId, ServiceDomainEvent::LEAD_QUALIFIED, $projek, [
            'project_no' => $projek->project_no,
            'title' => $projek->title,
            'client_id' => $projek->client_id,
        ]);

        return $projek;
    }

    /**
     * Tukar status projek dengan pengesahan aliran.
     *
     * @throws \RuntimeException jika peralihan tidak sah
     */
    public function tukarStatus(int $tenantId, int $projectId, string $statusBaharu, ?string $sebab = null): ServiceProject
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        if ($projek->status === $statusBaharu) {
            return $projek;
        }

        if (! $projek->bolehTukarKe($statusBaharu)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$projek->status} → {$statusBaharu}."
            );
        }

        $kemasKini = ['status' => $statusBaharu];

        if ($statusBaharu === ServiceProject::STATUS_COMPLETED) {
            $kemasKini['completed_at'] = now();
            $kemasKini['progress_percent'] = 100;
        }

        if ($statusBaharu === ServiceProject::STATUS_CANCELLED) {
            $kemasKini['cancelled_at'] = now();
            $kemasKini['cancellation_reason'] = $sebab;
        }

        if ($statusBaharu === ServiceProject::STATUS_IN_PROGRESS && ! $projek->start_date) {
            $kemasKini['start_date'] = now()->toDateString();
        }

        $projek->update($kemasKini);

        $peristiwa = match ($statusBaharu) {
            ServiceProject::STATUS_IN_PROGRESS => ServiceDomainEvent::PROJECT_STARTED,
            ServiceProject::STATUS_COMPLETED => ServiceDomainEvent::PROJECT_COMPLETED,
            ServiceProject::STATUS_CONFIRMED => ServiceDomainEvent::BOOKING_CONFIRMED,
            default => null,
        };

        if ($peristiwa) {
            $this->rekodPeristiwa($tenantId, $peristiwa, $projek, [
                'status' => $statusBaharu,
                'sebab' => $sebab,
            ]);
        }

        return $projek->fresh();
    }

    /**
     * Kira margin projek (kontrak − kos sebenar).
     */
    public function kiraMargin(int $tenantId, int $projectId): ServiceProject
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        $nilai = (float) $projek->contract_value;
        $kos = (float) $projek->actual_cost;
        $margin = round($nilai - $kos, 2);
        $peratus = $nilai > 0 ? round(($margin / $nilai) * 100, 2) : 0.0;

        $projek->update([
            'margin_amount' => $margin,
            'margin_percent' => $peratus,
        ]);

        $this->rekodPeristiwa($tenantId, ServiceDomainEvent::PROJECT_MARGIN_CALCULATED, $projek, [
            'contract_value' => $nilai,
            'actual_cost' => $kos,
            'margin_amount' => $margin,
            'margin_percent' => $peratus,
        ]);

        return $projek->fresh();
    }

    /**
     * Kira kos sebenar daripada penugasan kru + peralatan.
     *
     * NOTA: TIDAK termasuk pembayaran/faktur — itu domain Invoicing.
     */
    public function kiraKosSebenar(int $tenantId, int $projectId): float
    {
        $assignments = ServiceAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('service_project_id', $projectId)
            ->get();

        $kosKru = $assignments->sum(fn ($a) => (float) ($a->agreed_rate ?? 0));

        return round((float) $kosKru, 2);
    }

    /**
     * Semak konflik jadual projek dalam tenant.
     */
    public function semakKonflik(int $tenantId, int $projectId): array
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        if (! $projek->start_date || ! $projek->end_date) {
            return [];
        }

        $konflik = [];

        $lain = ServiceProject::query()
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $projectId)
            ->whereNotIn('status', [ServiceProject::STATUS_CANCELLED, ServiceProject::STATUS_COMPLETED])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where('start_date', '<=', $projek->end_date)
            ->where('end_date', '>=', $projek->start_date)
            ->get();

        foreach ($lain as $p) {
            $konflik[] = [
                'project_id' => $p->id,
                'project_no' => $p->project_no,
                'title' => $p->title,
                'start_date' => $p->start_date?->toDateString(),
                'end_date' => $p->end_date?->toDateString(),
                'status' => $p->status,
            ];
        }

        if ($konflik) {
            $this->rekodPeristiwa($tenantId, ServiceDomainEvent::SCHEDULE_CONFLICT_DETECTED, $projek, [
                'konflik' => count($konflik),
                'project_ids' => array_column($konflik, 'project_id'),
            ]);
        }

        return $konflik;
    }

    // ---------------------------------------------------------------
    // SESI
    // ---------------------------------------------------------------

    public function ciptaSesi(int $tenantId, int $projectId, array $data): ServiceSession
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        $bilangan = ServiceSession::query()
            ->where('tenant_id', $tenantId)
            ->where('service_project_id', $projectId)
            ->count();

        if ($bilangan >= self::MAX_SESSIONS_PER_PROJECT) {
            throw new \RuntimeException('Had sesi per projek dicapai (' . self::MAX_SESSIONS_PER_PROJECT . ').');
        }

        return ServiceSession::create(array_merge([
            'tenant_id' => $tenantId,
            'service_project_id' => $projectId,
            'session_type' => ServiceSession::TYPE_MAIN,
            'status' => ServiceSession::STATUS_PLANNED,
            'sequence' => $bilangan + 1,
        ], $data, [
            'tenant_id' => $tenantId,
            'service_project_id' => $projek->id,
        ]));
    }

    /**
     * Semak sesi kru yang bertindih bagi satu sesi.
     *
     * Logik: untuk SETIAP kru yang ditugaskan pada sesi ini, adakah kru
     * yang sama juga ditugaskan pada sesi LAIN yang bertindih masa?
     */
    public function konflikKruSesi(int $tenantId, int $sessionId): array
    {
        $sesi = ServiceSession::query()->where('tenant_id', $tenantId)->findOrFail($sessionId);

        if (! $sesi->start_at || ! $sesi->end_at) {
            return [];
        }

        $crewIds = ServiceAssignment::query()
            ->where('tenant_id', $tenantId)
            ->where('service_session_id', $sessionId)
            ->whereNotIn('status', [ServiceAssignment::STATUS_CANCELLED, ServiceAssignment::STATUS_DECLINED])
            ->pluck('crew_id')
            ->all();

        if ($crewIds === []) {
            return [];
        }

        $sesiLain = ServiceSession::query()
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $sessionId)
            ->where('status', '!=', ServiceSession::STATUS_CANCELLED)
            ->where('start_at', '<', $sesi->end_at)
            ->where('end_at', '>', $sesi->start_at)
            ->pluck('id')
            ->all();

        if ($sesiLain === []) {
            return [];
        }

        $penugasan = ServiceAssignment::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('service_session_id', $sesiLain)
            ->whereIn('crew_id', $crewIds)
            ->whereNotIn('status', [ServiceAssignment::STATUS_CANCELLED, ServiceAssignment::STATUS_DECLINED])
            ->get();

        return $penugasan->map(fn ($a) => [
            'crew_id' => $a->crew_id,
            'session_id' => $a->service_session_id,
            'start_at' => $a->start_at?->toIso8601String(),
            'end_at' => $a->end_at?->toIso8601String(),
        ])->values()->all();
    }

    // ---------------------------------------------------------------
    // SEBUT HARGA
    // ---------------------------------------------------------------

    /**
     * Cipta sebut harga baharu (versi seterusnya jika sudah ada).
     */
    public function ciptaSebutHarga(int $tenantId, int $projectId, array $data): ServiceQuotation
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        // `tax_rate` bukan kolum jadual — ambil dahulu, buang daripada payload.
        $kadarCukai = (float) ($data['tax_rate'] ?? 0);
        unset($data['tax_rate']);

        return DB::transaction(function () use ($tenantId, $projek, $data, $kadarCukai) {
            $versi = (int) ServiceQuotation::query()
                ->where('tenant_id', $tenantId)
                ->where('service_project_id', $projek->id)
                ->max('version');

            // Nyahaktifkan versi lama
            ServiceQuotation::query()
                ->where('tenant_id', $tenantId)
                ->where('service_project_id', $projek->id)
                ->update(['is_current' => false]);

            $sebutHarga = ServiceQuotation::create(array_merge([
                'tenant_id' => $tenantId,
                'service_project_id' => $projek->id,
                'client_id' => $projek->client_id,
                'status' => ServiceQuotation::STATUS_DRAFT,
                'currency' => 'MYR',
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'deposit_amount' => 0,
                'balance_amount' => 0,
                'is_current' => true,
            ], $data, [
                'tenant_id' => $tenantId,
                'service_project_id' => $projek->id,
                'version' => $versi + 1,
                'quote_no' => $this->janaNoSebutHarga($tenantId, $versi + 1),
                'is_current' => true,
            ]));

            // Kira semula jumlah daripada line items
            $kira = $sebutHarga->kiraJumlah($kadarCukai);
            $sebutHarga->update($kira);

            $this->rekodPeristiwa($tenantId, ServiceDomainEvent::QUOTATION_CREATED, $sebutHarga, [
                'quote_no' => $sebutHarga->quote_no,
                'version' => $sebutHarga->version,
                'total_amount' => (float) $sebutHarga->total_amount,
            ]);

            return $sebutHarga->fresh();
        });
    }

    /**
     * Terima sebut harga → cipta kontrak + tukar status projek.
     */
    public function terimaSebutHarga(int $tenantId, int $quotationId, array $penerima = []): ServiceContract
    {
        return DB::transaction(function () use ($tenantId, $quotationId, $penerima) {
            $sebutHarga = ServiceQuotation::query()
                ->where('tenant_id', $tenantId)
                ->findOrFail($quotationId);

            if ($sebutHarga->status === ServiceQuotation::STATUS_ACCEPTED) {
                // Idempoten — pulangkan kontrak sedia ada
                $sedia = ServiceContract::query()
                    ->where('tenant_id', $tenantId)
                    ->where('service_quotation_id', $sebutHarga->id)
                    ->first();

                if ($sedia) {
                    return $sedia;
                }
            }

            $sebutHarga->update([
                'status' => ServiceQuotation::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);

            $projek = ServiceProject::query()
                ->where('tenant_id', $tenantId)
                ->findOrFail($sebutHarga->service_project_id);

            $kontrak = ServiceContract::create([
                'tenant_id' => $tenantId,
                'service_project_id' => $projek->id,
                'service_quotation_id' => $sebutHarga->id,
                'client_id' => $sebutHarga->client_id,
                'contract_no' => $this->janaNoKontrak($tenantId),
                'status' => ServiceContract::STATUS_ACCEPTED,
                'currency' => $sebutHarga->currency,
                'contract_value' => $sebutHarga->total_amount,
                'deposit_amount' => $sebutHarga->deposit_amount,
                'accepted_at' => now(),
                'accepted_by_name' => $penerima['name'] ?? null,
                'accepted_by_email' => $penerima['email'] ?? null,
                'accepted_ip' => $penerima['ip'] ?? null,
            ]);

            $projek->update([
                'contract_value' => $sebutHarga->total_amount,
                'status' => ServiceProject::STATUS_CONFIRMED,
            ]);

            $this->rekodPeristiwa($tenantId, ServiceDomainEvent::QUOTATION_ACCEPTED, $sebutHarga, [
                'quote_no' => $sebutHarga->quote_no,
                'contract_no' => $kontrak->contract_no,
            ]);

            $this->rekodPeristiwa($tenantId, ServiceDomainEvent::CONTRACT_ACCEPTED, $kontrak, [
                'contract_no' => $kontrak->contract_no,
                'contract_value' => (float) $kontrak->contract_value,
            ]);

            return $kontrak->fresh();
        });
    }

    /**
     * Tolak sebut harga.
     */
    public function tolakSebutHarga(int $tenantId, int $quotationId, ?string $sebab = null): ServiceQuotation
    {
        $sebutHarga = ServiceQuotation::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($quotationId);

        $sebutHarga->update([
            'status' => ServiceQuotation::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $sebab,
        ]);

        return $sebutHarga->fresh();
    }

    // ---------------------------------------------------------------
    // KRU & PENUGASAN
    // ---------------------------------------------------------------

    public function ciptaKru(int $tenantId, array $data): ServiceCrew
    {
        return ServiceCrew::create(array_merge([
            'tenant_id' => $tenantId,
            'role' => ServiceCrew::ROLE_CREW,
            'day_rate' => 0,
            'hour_rate' => 0,
            'is_freelance' => false,
            'is_active' => true,
        ], $data, ['tenant_id' => $tenantId]));
    }

    /**
     * Tugaskan kru ke sesi. Menolak jika konflik masa.
     *
     * @throws \RuntimeException jika kru sudah ada penugasan bertindih
     */
    public function tugsakanKru(int $tenantId, int $projectId, int $crewId, array $data = []): ServiceAssignment
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        $crew = ServiceCrew::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->findOrFail($crewId);

        $sesiId = $data['service_session_id'] ?? null;
        $mula = isset($data['start_at']) ? Carbon::parse($data['start_at']) : null;
        $tamat = isset($data['end_at']) ? Carbon::parse($data['end_at']) : null;
        $peranan = $data['role'] ?? $crew->role;

        // Jika sesi diberikan, ambil masa daripada sesi
        if ($sesiId) {
            $sesi = ServiceSession::query()
                ->where('tenant_id', $tenantId)
                ->where('service_project_id', $projek->id)
                ->findOrFail($sesiId);

            $mula = $mula ?? $sesi->start_at;
            $tamat = $tamat ?? $sesi->end_at;
        }

        // Semak konflik masa kru
        if ($mula && $tamat) {
            $konflik = ServiceAssignment::query()
                ->where('tenant_id', $tenantId)
                ->where('crew_id', $crew->id)
                ->whereNotIn('status', [ServiceAssignment::STATUS_CANCELLED, ServiceAssignment::STATUS_DECLINED])
                ->whereNotNull('start_at')
                ->whereNotNull('end_at')
                ->where('start_at', '<', $tamat)
                ->where('end_at', '>', $mula)
                ->exists();

            if ($konflik) {
                throw new \RuntimeException(
                    "Kru {$crew->name} sudah mempunyai penugasan pada waktu tersebut."
                );
            }
        }

        $penugasan = ServiceAssignment::create(array_merge([
            'tenant_id' => $tenantId,
            'service_project_id' => $projek->id,
            'crew_id' => $crew->id,
            'role' => $peranan,
            'status' => ServiceAssignment::STATUS_ASSIGNED,
            'agreed_rate' => $crew->day_rate,
        ], $data, [
            'tenant_id' => $tenantId,
            'service_project_id' => $projek->id,
            'crew_id' => $crew->id,
            'role' => $peranan,
            'start_at' => $mula,
            'end_at' => $tamat,
        ]));

        $this->rekodPeristiwa($tenantId, ServiceDomainEvent::ASSIGNMENT_CREATED, $penugasan, [
            'crew_id' => $crew->id,
            'crew_name' => $crew->name,
            'role' => $peranan,
        ]);

        return $penugasan;
    }

    /**
     * Cadang kru terbaik untuk sesi berdasarkan kemahiran + ketersediaan.
     */
    public function cadangKru(int $tenantId, int $projectId, ?string $kemahiran = null, int $had = 5): array
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        $sesi = ServiceSession::query()
            ->where('tenant_id', $tenantId)
            ->where('service_project_id', $projek->id)
            ->orderBy('sequence')
            ->first();

        $mula = $sesi?->start_at;
        $tamat = $sesi?->end_at;

        $semua = ServiceCrew::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $calon = [];

        foreach ($semua as $crew) {
            if ($kemahiran !== null && ! $crew->adaKemahiran($kemahiran)) {
                continue;
            }

            $sibuk = false;

            if ($mula && $tamat) {
                $sibuk = ServiceAssignment::query()
                    ->where('tenant_id', $tenantId)
                    ->where('crew_id', $crew->id)
                    ->whereNotIn('status', [ServiceAssignment::STATUS_CANCELLED, ServiceAssignment::STATUS_DECLINED])
                    ->whereNotNull('start_at')
                    ->whereNotNull('end_at')
                    ->where('start_at', '<', $tamat)
                    ->where('end_at', '>', $mula)
                    ->exists();
            }

            if ($sibuk) {
                continue;
            }

            $calon[] = [
                'crew_id' => $crew->id,
                'name' => $crew->name,
                'role' => $crew->role,
                'day_rate' => (float) $crew->day_rate,
                'is_freelance' => (bool) $crew->is_freelance,
                'skills' => (array) ($crew->skills ?? []),
            ];
        }

        // Isih: harga harian paling rendah dahulu
        usort($calon, fn ($a, $b) => $a['day_rate'] <=> $b['day_rate']);

        return array_slice($calon, 0, $had);
    }

    // ---------------------------------------------------------------
    // TUGAS
    // ---------------------------------------------------------------

    /**
     * Cipta tugas / checklist.
     */
    public function ciptaTugas(int $tenantId, int $projectId, array $data): ServiceTask
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        $bilangan = ServiceTask::query()
            ->where('tenant_id', $tenantId)
            ->where('service_project_id', $projectId)
            ->count();

        if ($bilangan >= self::MAX_TASKS_PER_PROJECT) {
            throw new \RuntimeException('Had tugas per projek dicapai (' . self::MAX_TASKS_PER_PROJECT . ').');
        }

        return ServiceTask::create(array_merge([
            'tenant_id' => $tenantId,
            'service_project_id' => $projek->id,
            'phase' => ServiceTask::PHASE_PREPARATION,
            'status' => ServiceTask::STATUS_PENDING,
            'priority' => ServiceProject::PRIORITY_NORMAL,
            'sequence' => $bilangan + 1,
            'is_mandatory' => false,
        ], $data, [
            'tenant_id' => $tenantId,
            'service_project_id' => $projek->id,
        ]));
    }

    /**
     * Jana checklist standard ikut jenis industri.
     */
    public function janaChecklist(int $tenantId, int $projectId, string $industri = 'umum'): array
    {
        $templat = self::TEMPLAT_CHECKLIST[$industri] ?? self::TEMPLAT_CHECKLIST['umum'];
        $dicipta = [];

        foreach ($templat as $i => $tugas) {
            $dicipta[] = $this->ciptaTugas($tenantId, $projectId, [
                'title' => $tugas['title'],
                'phase' => $tugas['phase'],
                'is_mandatory' => $tugas['mandatory'] ?? false,
                'sequence' => $i + 1,
            ]);
        }

        return $dicipta;
    }

    /**
     * Tandakan tugas selesai dan kira semula kemajuan projek.
     */
    public function siapkanTugas(int $tenantId, int $taskId): ServiceTask
    {
        $tugas = ServiceTask::query()->where('tenant_id', $tenantId)->findOrFail($taskId);

        $tugas->update([
            'status' => ServiceTask::STATUS_DONE,
            'completed_at' => now(),
        ]);

        $this->kiraSemulaKemajuan($tenantId, (int) $tugas->service_project_id);

        return $tugas->fresh();
    }

    /**
     * Kira semula progress_percent projek daripada tugas selesai.
     */
    public function kiraSemulaKemajuan(int $tenantId, int $projectId): ServiceProject
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        $jumlah = ServiceTask::query()
            ->where('tenant_id', $tenantId)
            ->where('service_project_id', $projectId)
            ->count();

        if ($jumlah === 0) {
            return $projek;
        }

        $selesai = ServiceTask::query()
            ->where('tenant_id', $tenantId)
            ->where('service_project_id', $projectId)
            ->whereIn('status', [ServiceTask::STATUS_DONE, ServiceTask::STATUS_SKIPPED])
            ->count();

        $projek->update(['progress_percent' => (int) round(($selesai / $jumlah) * 100)]);

        return $projek->fresh();
    }

    /**
     * Tugas lewat (melepasi due_at).
     */
    public function tugasLewat(int $tenantId, ?int $projectId = null): array
    {
        $query = ServiceTask::query()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', [ServiceTask::STATUS_DONE, ServiceTask::STATUS_SKIPPED])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());

        if ($projectId) {
            $query->where('service_project_id', $projectId);
        }

        return $query->orderBy('due_at')->get()->map(fn ($t) => [
            'task_id' => $t->id,
            'project_id' => $t->service_project_id,
            'title' => $t->title,
            'phase' => $t->phase,
            'due_at' => $t->due_at?->toIso8601String(),
            'hari_lewat' => $t->due_at ? (int) $t->due_at->diffInDays(now()) : 0,
            'is_mandatory' => (bool) $t->is_mandatory,
        ])->values()->all();
    }

    // ---------------------------------------------------------------
    // PERALATAN
    // ---------------------------------------------------------------

    public function ciptaPeralatan(int $tenantId, array $data): ServiceEquipment
    {
        return ServiceEquipment::create(array_merge([
            'tenant_id' => $tenantId,
            'quantity_total' => 1,
            'replacement_value' => 0,
            'requires_maintenance' => false,
            'is_active' => true,
        ], $data, ['tenant_id' => $tenantId]));
    }

    /**
     * Tempah peralatan — gagal jika kuantiti tidak mencukupi.
     *
     * @throws \RuntimeException jika stok peralatan tidak cukup
     */
    public function tempahPeralatan(int $tenantId, int $projectId, int $equipmentId, array $data): ServiceEquipmentReservation
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        $peralatan = ServiceEquipment::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($equipmentId);

        $dari = Carbon::parse($data['reserved_from'] ?? now());
        $hingga = Carbon::parse($data['reserved_to'] ?? now()->addDay());
        $kuantiti = (int) ($data['quantity'] ?? 1);

        $baki = $peralatan->bakiUntukTempoh($dari, $hingga);

        if ($kuantiti > $baki) {
            throw new \RuntimeException(
                "Stok peralatan '{$peralatan->name}' tidak mencukupi: diminta {$kuantiti}, baki {$baki}."
            );
        }

        return ServiceEquipmentReservation::create(array_merge([
            'tenant_id' => $tenantId,
            'service_equipment_id' => $peralatan->id,
            'service_project_id' => $projek->id,
            'quantity' => $kuantiti,
            'reserved_from' => $dari,
            'reserved_to' => $hingga,
            'status' => ServiceEquipmentReservation::STATUS_RESERVED,
        ], $data, [
            'tenant_id' => $tenantId,
            'service_equipment_id' => $peralatan->id,
            'service_project_id' => $projek->id,
            'quantity' => $kuantiti,
            'reserved_from' => $dari,
            'reserved_to' => $hingga,
        ]));
    }

    // ---------------------------------------------------------------
    // PENYERAHAN
    // ---------------------------------------------------------------

    public function ciptaPenyerahan(int $tenantId, int $projectId, array $data): ServiceDelivery
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->findOrFail($projectId);

        return ServiceDelivery::create(array_merge([
            'tenant_id' => $tenantId,
            'service_project_id' => $projek->id,
            'delivery_type' => ServiceDelivery::TYPE_HANDOVER,
            'status' => ServiceDelivery::STATUS_PENDING,
        ], $data, [
            'tenant_id' => $tenantId,
            'service_project_id' => $projek->id,
        ]));
    }

    /**
     * Terima penyerahan → tukar projek ke delivered.
     */
    public function terimaPenyerahan(int $tenantId, int $deliveryId, ?string $namaPenerima = null): ServiceDelivery
    {
        return DB::transaction(function () use ($tenantId, $deliveryId, $namaPenerima) {
            $penyerahan = ServiceDelivery::query()
                ->where('tenant_id', $tenantId)
                ->findOrFail($deliveryId);

            $penyerahan->update([
                'status' => ServiceDelivery::STATUS_ACCEPTED,
                'delivered_at' => $penyerahan->delivered_at ?? now(),
                'received_by_name' => $namaPenerima ?? $penyerahan->received_by_name,
            ]);

            $projek = ServiceProject::query()
                ->where('tenant_id', $tenantId)
                ->findOrFail($penyerahan->service_project_id);

            if ($projek->bolehTukarKe(ServiceProject::STATUS_DELIVERED)) {
                $projek->update(['status' => ServiceProject::STATUS_DELIVERED]);
            }

            $this->rekodPeristiwa($tenantId, ServiceDomainEvent::DELIVERY_ACCEPTED, $penyerahan, [
                'delivery_type' => $penyerahan->delivery_type,
                'received_by' => $namaPenerima,
            ]);

            return $penyerahan->fresh();
        });
    }

    // ---------------------------------------------------------------
    // PERISTIWA DOMAIN (OUTBOX)
    // ---------------------------------------------------------------

    /**
     * Rekod peristiwa domain ke outbox.
     *
     * `idempotency_key` menjamin peristiwa sama tidak direkod dua kali.
     */
    public function rekodPeristiwa(int $tenantId, string $nama, object $aggregate, array $payload = [], ?string $idemKey = null): ServiceDomainEvent
    {
        $jenis = class_basename($aggregate);
        $id = $aggregate->id ?? 0;
        $kunci = $idemKey ?? Str::slug("{$nama}-{$jenis}-{$id}-" . md5(json_encode($payload)));

        $sedia = ServiceDomainEvent::query()
            ->where('tenant_id', $tenantId)
            ->where('idempotency_key', $kunci)
            ->first();

        if ($sedia) {
            return $sedia;
        }

        return ServiceDomainEvent::create([
            'tenant_id' => $tenantId,
            'event_name' => $nama,
            'aggregate_type' => $jenis,
            'aggregate_id' => $id,
            'payload' => $payload,
            'status' => ServiceDomainEvent::STATUS_PENDING,
            'attempts' => 0,
            'idempotency_key' => $kunci,
        ]);
    }

    /**
     * Proses outbox — tandakan `dispatched`.
     *
     * Pemproses sebenar (WorkflowStudio/Reminder) boleh melanggan di sini.
     */
    public function prosesOutbox(int $tenantId): array
    {
        $peristiwa = ServiceDomainEvent::query()
            ->where('tenant_id', $tenantId)
            ->where('status', ServiceDomainEvent::STATUS_PENDING)
            ->where('attempts', '<', 3)
            ->orderBy('id')
            ->limit(self::MAX_OUTBOX_BATCH)
            ->get();

        $diproses = 0;
        $gagal = 0;

        foreach ($peristiwa as $e) {
            try {
                $e->update([
                    'status' => ServiceDomainEvent::STATUS_DISPATCHED,
                    'dispatched_at' => now(),
                    'attempts' => $e->attempts + 1,
                ]);
                $diproses++;
            } catch (\Throwable $ex) {
                $e->update([
                    'status' => ServiceDomainEvent::STATUS_FAILED,
                    'attempts' => $e->attempts + 1,
                    'last_error' => mb_substr($ex->getMessage(), 0, 500),
                ]);
                $gagal++;
                Log::warning('ServiceOS: proses outbox gagal', [
                    'tenant_id' => $tenantId,
                    'event_id' => $e->id,
                    'error' => $ex->getMessage(),
                ]);
            }
        }

        return ['diproses' => $diproses, 'gagal' => $gagal];
    }

    // ---------------------------------------------------------------
    // RINGKASAN / DASHBOARD
    // ---------------------------------------------------------------

    /**
     * Ringkasan operasi untuk dashboard / AI COO.
     */
    public function ringkasan(int $tenantId): array
    {
        $projek = ServiceProject::query()->where('tenant_id', $tenantId)->get();

        $ikutStatus = $projek->groupBy('status')->map->count();
        $aktif = $projek->filter(fn ($p) => $p->masihAktif());

        $nilaiAktif = $aktif->sum(fn ($p) => (float) $p->contract_value);
        $marginAktif = $aktif->sum(fn ($p) => (float) $p->margin_amount);

        $tugasLewat = ServiceTask::query()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', [ServiceTask::STATUS_DONE, ServiceTask::STATUS_SKIPPED])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        return [
            'jumlah_projek' => $projek->count(),
            'projek_aktif' => $aktif->count(),
            'ikut_status' => $ikutStatus->toArray(),
            'nilai_kontrak_aktif' => round((float) $nilaiAktif, 2),
            'margin_aktif' => round((float) $marginAktif, 2),
            'tugas_lewat' => $tugasLewat,
            'sebut_harga_menunggu' => ServiceQuotation::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('status', [ServiceQuotation::STATUS_DRAFT, ServiceQuotation::STATUS_SENT])
                ->count(),
            'peristiwa_tertunggak' => ServiceDomainEvent::query()
                ->where('tenant_id', $tenantId)
                ->where('status', ServiceDomainEvent::STATUS_PENDING)
                ->count(),
            'pelanggan_aktif' => ServiceClient::query()
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->count(),
        ];
    }

    // ---------------------------------------------------------------
    // PENJANA NOMBOR
    // ---------------------------------------------------------------

    public function janaNoProjek(int $tenantId): string
    {
        $bil = ServiceProject::withTrashed()
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('SVP-%s-%04d', now()->format('Y'), $bil);
    }

    public function janaNoSebutHarga(int $tenantId, int $versi = 1): string
    {
        $bil = ServiceQuotation::query()
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('SVQ-%s-%04d-V%d', now()->format('Y'), $bil, $versi);
    }

    public function janaNoKontrak(int $tenantId): string
    {
        $bil = ServiceContract::query()
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('SVC-%s-%04d', now()->format('Y'), $bil);
    }

    // ---------------------------------------------------------------
    // TEMPLAT CHECKLIST
    // ---------------------------------------------------------------

    /** Checklist standard ikut industri (BAB 87.2). */
    public const TEMPLAT_CHECKLIST = [
        'fotografi' => [
            ['title' => 'Sahkan tarikh & lokasi dengan pelanggan', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Semak senarai shot yang diminta', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Caj bateri kamera & format kad memori', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Tempah peralatan & backup body', 'phase' => ServiceTask::PHASE_PREPARATION],
            ['title' => 'Hadir di lokasi 60 minit awal', 'phase' => ServiceTask::PHASE_EXECUTION, 'mandatory' => true],
            ['title' => 'Backup fail sejurus selepas sesi', 'phase' => ServiceTask::PHASE_POST, 'mandatory' => true],
            ['title' => 'Sunting & pilih gambar terbaik', 'phase' => ServiceTask::PHASE_POST],
            ['title' => 'Serah galeri kepada pelanggan', 'phase' => ServiceTask::PHASE_POST, 'mandatory' => true],
        ],
        'wedding' => [
            ['title' => 'Konsultasi konsep & tema', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Jadual setup pelamin & dewan', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Sahkan senarai kru & vendor', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Setup dewan mengikut pelan', 'phase' => ServiceTask::PHASE_EXECUTION, 'mandatory' => true],
            ['title' => 'Briefing kru sebelum majlis', 'phase' => ServiceTask::PHASE_EXECUTION],
            ['title' => 'Tear-down & pemulihan dewan', 'phase' => ServiceTask::PHASE_POST, 'mandatory' => true],
            ['title' => 'Pulangkan deposit dewan (jika ada)', 'phase' => ServiceTask::PHASE_ADMIN],
        ],
        'kontraktor' => [
            ['title' => 'Lawatan tapak & ukuran', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Sediakan sebut harga terperinci', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Dapatkan permit kerja (jika perlu)', 'phase' => ServiceTask::PHASE_ADMIN],
            ['title' => 'Beli bahan & sewa peralatan', 'phase' => ServiceTask::PHASE_PREPARATION],
            ['title' => 'Jalankan kerja mengikut skop', 'phase' => ServiceTask::PHASE_EXECUTION, 'mandatory' => true],
            ['title' => 'Pemeriksaan kualiti dalaman', 'phase' => ServiceTask::PHASE_POST, 'mandatory' => true],
            ['title' => 'Handover & tandatangan penerimaan', 'phase' => ServiceTask::PHASE_POST, 'mandatory' => true],
        ],
        'katering' => [
            ['title' => 'Sahkan bilangan tetamu & menu', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Senarai bahan & pembelian', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Sahkan peralatan memasak & pemanas', 'phase' => ServiceTask::PHASE_PREPARATION],
            ['title' => 'Masak mengikut jadual', 'phase' => ServiceTask::PHASE_EXECUTION, 'mandatory' => true],
            ['title' => 'Hantar & setup buffet', 'phase' => ServiceTask::PHASE_EXECUTION, 'mandatory' => true],
            ['title' => 'Kutip & basuh peralatan', 'phase' => ServiceTask::PHASE_POST, 'mandatory' => true],
        ],
        'umum' => [
            ['title' => 'Sahkan keperluan dengan pelanggan', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Sediakan sebut harga', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Tetapkan jadual kerja', 'phase' => ServiceTask::PHASE_PREPARATION, 'mandatory' => true],
            ['title' => 'Jalankan kerja', 'phase' => ServiceTask::PHASE_EXECUTION, 'mandatory' => true],
            ['title' => 'Semakan kualiti', 'phase' => ServiceTask::PHASE_POST],
            ['title' => 'Serah kepada pelanggan', 'phase' => ServiceTask::PHASE_POST, 'mandatory' => true],
        ],
    ];

    /** Senarai industri yang ada templat. */
    public function senaraiIndustri(): array
    {
        return array_keys(self::TEMPLAT_CHECKLIST);
    }
}
