<?php

namespace App\Modules\Reminder\Services;

use App\Modules\Reminder\Models\Reminder;
use App\Modules\Reminder\Models\ReminderDelivery;
use App\Modules\Reminder\Models\ReminderInbox;
use App\Modules\Reminder\Models\ReminderSchedule;
use App\Modules\Reminder\Models\ReminderType;
use Illuminate\Support\Facades\Log;

/**
 * ReminderService — pusat peringatan merentas semua modul.
 *
 * ✅ Idea #22 (BAB 86): "reminder" — tenant tidakyraHetak "bila nak hubungi
 * pelanggan?" dengan menyemak 7 jadual. Semua modul menghantar peringatan
 * ke sini; AI triase menentukan mana yang benar-benar penting.
 */
class ReminderService
{
    /**
     * Katalog jenis peringatan lalai.
     *
     * @var array<string, array{name: string, category: string, priority: string, lead_hours: int, channels: array}>
     */
    public const KATALOG = [
        'payment_due' => ['name' => 'Bayaran Belum Settle', 'category' => 'finance', 'priority' => Reminder::PRIORITY_HIGH, 'lead_hours' => 24, 'channels' => ['in_app', 'email']],
        'order_follow_up' => ['name' => 'Susulan Pesanan', 'category' => 'sales', 'priority' => Reminder::PRIORITY_NORMAL, 'lead_hours' => 12, 'channels' => ['in_app', 'email']],
        'cod_collection' => ['name' => 'Kutipan COD Tertunggak', 'category' => 'finance', 'priority' => Reminder::PRIORITY_HIGH, 'lead_hours' => 6, 'channels' => ['in_app', 'whatsapp']],
        'invoice_overdue' => ['name' => 'Invois Lewat Bayar', 'category' => 'finance', 'priority' => Reminder::PRIORITY_URGENT, 'lead_hours' => 48, 'channels' => ['in_app', 'email', 'whatsapp']],
        'contract_renewal' => ['name' => 'Pembaharuan Kontrak', 'category' => 'legal', 'priority' => Reminder::PRIORITY_NORMAL, 'lead_hours' => 168, 'channels' => ['in_app', 'email']],
        'stock_low' => ['name' => 'Stok Rendah', 'category' => 'ops', 'priority' => Reminder::PRIORITY_NORMAL, 'lead_hours' => 4, 'channels' => ['in_app']],
        'stock_out' => ['name' => 'Stok Habis', 'category' => 'ops', 'priority' => Reminder::PRIORITY_HIGH, 'lead_hours' => 1, 'channels' => ['in_app', 'email']],
        'leave_approval' => ['name' => 'Kelulusan Cuti', 'category' => 'hr', 'priority' => Reminder::PRIORITY_NORMAL, 'lead_hours' => 24, 'channels' => ['in_app', 'email']],
        'payroll_approval' => ['name' => 'Kelulusan Gaji', 'category' => 'hr', 'priority' => Reminder::PRIORITY_HIGH, 'lead_hours' => 72, 'channels' => ['in_app', 'email']],
        'compliance_deadline' => ['name' => 'Tarikh Pematuhan', 'category' => 'compliance', 'priority' => Reminder::PRIORITY_URGENT, 'lead_hours' => 168, 'channels' => ['in_app', 'email']],
        'review_due' => ['name' => 'Semakan Pelanggan', 'category' => 'sales', 'priority' => Reminder::PRIORITY_LOW, 'lead_hours' => 72, 'channels' => ['in_app']],
        'chase_opportunity' => ['name' => 'Kejar Peluang', 'category' => 'sales', 'priority' => Reminder::PRIORITY_HIGH, 'lead_hours' => 6, 'channels' => ['in_app', 'email', 'whatsapp']],
        'maintenance_due' => ['name' => 'Penyelenggaraan Jatuh', 'category' => 'ops', 'priority' => Reminder::PRIORITY_NORMAL, 'lead_hours' => 24, 'channels' => ['in_app']],
        'follow_up_meeting' => ['name' => 'Mesyuarat Susulan', 'category' => 'general', 'priority' => Reminder::PRIORITY_NORMAL, 'lead_hours' => 2, 'channels' => ['in_app', 'push']],
    ];

    /**
     * Jana katalog jenis peringatan untuk tenant (idempotent).
     */
    public function janaKatalog(int $tenantId): int
    {
        $dicipta = 0;

        foreach (self::KATALOG as $key => $definisi) {
            $ada = ReminderType::query()
                ->where('tenant_id', $tenantId)
                ->where('key', $key)
                ->exists();

            if ($ada) {
                continue;
            }

            ReminderType::create([
                'tenant_id' => $tenantId,
                'key' => $key,
                'name' => $definisi['name'],
                'category' => $definisi['category'],
                'default_priority' => $definisi['priority'],
                'default_lead_hours' => $definisi['lead_hours'],
                'default_channels' => $definisi['channels'],
                'is_active' => true,
            ]);

            $dicipta++;
        }

        return $dicipta;
    }

    /**
     * Cipta peringatan.
     */
    public function cipta(int $tenantId, string $typeKey, array $data): Reminder
    {
        $type = ReminderType::query()
            ->where('tenant_id', $tenantId)
            ->where('key', $typeKey)
            ->first();

        $prioriti = $data['priority'] ?? $type?->default_priority ?? Reminder::PRIORITY_NORMAL;
        $channels = $data['channels'] ?? $type?->default_channels ?? ['in_app'];
        $leadHours = $data['lead_hours'] ?? $type?->default_lead_hours ?? 24;

        $dueAt = $data['due_at'] ?? null;
        if ($dueAt === null && ! empty($data['subject_due_at'])) {
            $dueAt = \Illuminate\Support\Carbon::parse($data['subject_due_at'])
                ->subHours((int) $leadHours);
        }

        $reminder = Reminder::create([
            'tenant_id' => $tenantId,
            'reminder_type_id' => $type?->id,
            'type_key' => $typeKey,
            'title' => $data['title'] ?? ($type?->name ?? $typeKey),
            'description' => $data['description'] ?? null,
            'action_note' => $data['action_note'] ?? null,
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'subject_label' => $data['subject_label'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'assigned_role' => $data['assigned_role'] ?? null,
            'channels' => $channels,
            'status' => Reminder::STATUS_PENDING,
            'priority' => $prioriti,
            'due_at' => $dueAt,
            'meta' => $data['meta'] ?? null,
        ]);

        // AI triase jika diperlukan
        if ($type?->requires_ai_triage) {
            $this->triageAI($tenantId, $reminder);
        }

        return $reminder->fresh();
    }

    /**
     * AI triase — tentukan keutamaan & hotline ke kotak masuk.
     */
    public function triageAI(int $tenantId, Reminder $reminder): array
    {
        try {
            $gateway = app(\App\Modules\AIGateway\Services\GatewayService::class);

            $hasil = $gateway->call(
                $tenantId,
                [
                    ['role' => 'system', 'content' => 'Andaotas penapis peringatan perniagaan.'],
                    ['role' => 'user', 'content' =>
                        "Peringatan: {$reminder->title}\n"
                        . ($reminder->description ?: '') . "\n"
                        . "Kategori: {$reminder->type_key}\n"
                        . 'Nilai keutamaan 0-100 (100 paling penting), dan alasan ringkas.'
                    ],
                ],
                'reminder_triage',
                ['max_output_tokens' => 200, 'source_module' => 'reminder']
            );

            $kandungan = (string) ($hasil['content'] ?? '');
            $skor = $this->ekstrakNombor($kandungan);

            $reminder->update([
                'ai_priority_score' => $skor,
                'ai_reasoning' => mb_substr(trim($kandungan), 0, 500),
                'ai_escalated' => $skor >= (float) config('reminder.ai.escalate_threshold', 75),
            ]);

            // Kotak masuk AI hanya untuk yang penting
            if ($skor >= (float) config('reminder.ai.inbox_threshold', 60)) {
                ReminderInbox::create([
                    'tenant_id' => $tenantId,
                    'reminder_id' => $reminder->id,
                    'headline' => $reminder->title,
                    'summary' => $reminder->description,
                    'urgency' => $reminder->priority,
                    'suggested_action' => $reminder->action_note,
                    'context' => [
                        'type' => $reminder->type_key,
                        'subject_type' => $reminder->subject_type,
                        'subject_id' => $reminder->subject_id,
                        'score' => $skor,
                    ],
                ]);
            }

            return ['skor' => $skor, 'escalated' => $reminder->fresh()->ai_escalated];
        } catch (\Throwable $e) {
            Log::warning('Reminder: triage AI gagal', ['error' => $e->getMessage()]);

            return ['skor' => null, 'escalated' => false];
        }
    }

    /**
     * Hantar peringatan melalui kanal.
     */
    public function hantar(int $tenantId, int $reminderId, ?array $channels = null): array
    {
        $reminder = Reminder::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($reminderId);

        $kanal = $channels ?? $reminder->channels ?? ['in_app'];
        $hasil = [];

        foreach ($kanal as $channel) {
            $mula = microtime(true);

            try {
                $kandungan = $this->binaKandungan($reminder);

                // In-app → kotak masuk
                if ($channel === ReminderDelivery::CHANNEL_IN_APP) {
                    ReminderInbox::firstOrCreate(
                        [
                            'tenant_id' => $tenantId,
                            'reminder_id' => $reminder->id,
                        ],
                        [
                            'headline' => $reminder->title,
                            'summary' => $reminder->description,
                            'urgency' => $reminder->priority,
                            'suggested_action' => $reminder->action_note,
                            'context' => [
                                'type' => $reminder->type_key,
                                'subject_type' => $reminder->subject_type,
                                'subject_id' => $reminder->subject_id,
                            ],
                        ]
                    );
                }

                // Saluran lain: integrasi provider (email/whatsapp/sms) — rekod stub
                ReminderDelivery::create([
                    'tenant_id' => $tenantId,
                    'reminder_id' => $reminder->id,
                    'channel' => $channel,
                    'status' => ReminderDelivery::STATUS_SENT,
                    'recipient' => $reminder->assigned_to ? (string) $reminder->assigned_to : null,
                    'content' => $kandungan,
                    'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
                ]);

                $hasil[$channel] = 'sent';
            } catch (\Throwable $e) {
                ReminderDelivery::create([
                    'tenant_id' => $tenantId,
                    'reminder_id' => $reminder->id,
                    'channel' => $channel,
                    'status' => ReminderDelivery::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                    'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
                ]);

                $hasil[$channel] = 'failed';
            }
        }

        $reminder->update([
            'status' => Reminder::STATUS_SENT,
            'sent_at' => now(),
            'reminder_count' => $reminder->reminder_count + 1,
        ]);

        return $hasil;
    }

    /**
     * Bina kandungan mesej peringatan.
     */
    private function binaKandungan(Reminder $reminder): string
    {
        $baris = [$reminder->title];

        if ($reminder->description) {
            $baris[] = $reminder->description;
        }

        if ($reminder->subject_label) {
            $baris[] = 'Rujukan: ' . $reminder->subject_label;
        }

        if ($reminder->action_note) {
            $baris[] = 'Tindakan: ' . $reminder->action_note;
        }

        if ($reminder->due_at) {
            $baris[] = 'Tarikh akhir: ' . $reminder->due_at->format('d M Y H:i');
        }

        return implode("\n", $baris);
    }

    /**
     * Tunda peringatan (snooze).
     */
    public function tunda(int $tenantId, int $reminderId, int $menit): Reminder
    {
        $reminder = Reminder::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($reminderId);

        $reminder->update([
            'status' => Reminder::STATUS_SNOOZED,
            'snoozed_until' => now()->addMinutes($menit),
        ]);

        return $reminder->fresh();
    }

    /**
     * Tunjukhierarchical prihatin.
     */
    public function sahkan(int $tenantId, int $reminderId): Reminder
    {
        $reminder = Reminder::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($reminderId);

        $reminder->update([
            'status' => Reminder::STATUS_ACKNOWLEDGED,
            'acknowledged_at' => now(),
        ]);

        return $reminder->fresh();
    }

    /**
     * Tengkapkan peringatan.
     */
    public function lengkapkan(int $tenantId, int $reminderId): Reminder
    {
        $reminder = Reminder::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($reminderId);

        $reminder->update([
            'status' => Reminder::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return $reminder->fresh();
    }

    /**
     * Hilangkan peringatan.
     */
    public function buang(int $tenantId, int $reminderId): Reminder
    {
        $reminder = Reminder::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($reminderId);

        $reminder->update(['status' => Reminder::STATUS_DISMISSED]);

        return $reminder->fresh();
    }

    /**
     * Eskalasi peringatan lewat (scheduler).
     */
    public function eskalasiLewat(int $tenantId): array
    {
        $lewat = Reminder::query()
            ->where('tenant_id', $tenantId)
            ->where('status', Reminder::STATUS_PENDING)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->get();

        foreach ($lewat as $reminder) {
            $reminder->update([
                'escalated_at' => now(),
                'escalation_count' => $reminder->escalation_count + 1,
                'priority' => $reminder->priority === Reminder::PRIORITY_NORMAL
                    ? Reminder::PRIORITY_HIGH
                    : $reminder->priority,
            ]);

            $this->hantar($tenantId, $reminder->id);
        }

        return ['dileskalakan' => $lewat->count()];
    }

    /**
     * Kotak masuk AI — peringatan paling penting.
     */
    public function kotakMasuk(int $tenantId, ?int $userId = null, bool $hanyaBelumDibaca = true): array
    {
        $query = ReminderInbox::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at');

        if ($hanyaBelumDibaca) {
            $query->where('is_read', false);
        }

        return $query->limit(50)->get()->toArray();
    }

    /**
     * Tanda kotak masuk sebagai dibaca.
     */
    public function tandakanDibaca(int $tenantId, int $inboxId): ReminderInbox
    {
        $item = ReminderInbox::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($inboxId);

        $item->update(['is_read' => true, 'read_at' => now()]);

        return $item->fresh();
    }

    /**
     * Ringkasan peringatan.
     */
    public function ringkasan(int $tenantId): array
    {
        $reminders = Reminder::query()->where('tenant_id', $tenantId)->get();

        $aktif = $reminders->filter(fn ($r) => $r->aktif());
        $lewat = $reminders->filter(fn ($r) => $r->lewat());

        return [
            'jumlah' => $reminders->count(),
            'aktif' => $aktif->count(),
            'lewat' => $lewat->count(),
            'menunggu_keputusan' => $reminders->where('status', Reminder::STATUS_SENT)->count(),
            'selesai' => $reminders->where('status', Reminder::STATUS_COMPLETED)->count(),
            'kotak_masuk_belum_dibaca' => ReminderInbox::query()
                ->where('tenant_id', $tenantId)
                ->where('is_read', false)
                ->count(),
            'ikut_kategori' => $reminders
                ->groupBy('type_key')
                ->map(fn ($g, $k) => ['type' => $k, 'bilangan' => $g->count()])
                ->toArray(),
            'ikut_prioriti' => $reminders
                ->whereIn('status', [Reminder::STATUS_PENDING, Reminder::STATUS_SENT])
                ->groupBy('priority')
                ->map(fn ($g, $k) => ['prioriti' => $k, 'bilangan' => $g->count()])
                ->toArray(),
        ];
    }

    /**
     * Jalankan jadwal berulang (scheduler).
     */
    public function jalankanJadual(int $tenantId): array
    {
        $jadual = ReminderSchedule::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            })
            ->get();

        $dijana = 0;

        foreach ($jadual as $s) {
            $reminder = $this->cipta($tenantId, $s->type_key, [
                'title' => $s->name,
                'channels' => $s->channels,
                'due_at' => now(),
                'meta' => ['schedule_id' => $s->id],
            ]);

            $this->hantar($tenantId, $reminder->id);

            $s->update([
                'last_run_at' => now(),
                'next_run_at' => $s->kiraRunSeterusnya(now()),
            ]);

            $dijana++;
        }

        return ['dijana' => $dijana, 'jadual' => $jadual->count()];
    }

    /**
     * Ekstrak nombor pertama daripada teks (untuk skor AI).
     */
    private function ekstrakNombor(string $teks): float
    {
        if (preg_match('/(\d{1,3})/', $teks, $m)) {
            return min(100.0, (float) $m[1]);
        }

        return 50.0;
    }
}
