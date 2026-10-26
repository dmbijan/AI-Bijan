<?php

namespace App\Modules\ExecutiveAI\Services;

/**
 * AutonomyService — mengawal tahap autonomy AI (BAB 82.8).
 *
 * 5 tahap:
 * 0 Tanya     — baca sahaja
 * 1 Cadang    — cadang tindakan
 * 2 Draf      — jana draf, perlu kelulusan
 * 3 Laksana   — eksekusi selepas satu klik kelulusan
 * 4 Automasi  — auto dalam had + audit penuh
 *
 * ⚠️ PRINSIP: Tiada tindakan boleh replyingMelebihi tahap tanpa kelulusan
 * manusia. Service ini adalah SATU-SATUNYA sumber kebenaran.
 */
class AutonomyService
{
    public const LEVEL_TANYA = 0;
    public const LEVEL_CADANG = 1;
    public const LEVEL_DRAF = 2;
    public const LEVEL_LAKSANA = 3;
    public const LEVEL_AUTOMASI = 4;

    /**
     * Tahap minimum untuk setiap jenis tindakan.
     *
     * Nilai yang lebih tinggi = lebih banyak kelulusan diperlukan.
     *
     * @var array<string, int>
     */
    public const TINDAKAN_MINIMUM = [
        // Baca sahaja
        'read_data' => self::LEVEL_TANYA,
        'read_dashboard' => self::LEVEL_TANYA,
        'ask_question' => self::LEVEL_TANYA,

        // Cadangan
        'suggest_action' => self::LEVEL_CADANG,
        'analyse' => self::LEVEL_CADANG,
        'score_prospect' => self::LEVEL_CADANG,

        // Draf — perlu diluluskan sebelum dihantar
        'draft_email' => self::LEVEL_DRAF,
        'draft_sms' => self::LEVEL_DRAF,
        'draft_whatsapp' => self::LEVEL_DRAF,
        'draft_video' => self::LEVEL_DRAF,
        'draft_proposal' => self::LEVEL_DRAF,
        'draft_price_change' => self::LEVEL_DRAF,

        // Tindakan luaran — perlu kelulusan satu klik
        'send_email' => self::LEVEL_LAKSANA,
        'send_sms' => self::LEVEL_LAKSANA,
        'send_whatsapp' => self::LEVEL_LAKSANA,
        'send_video_blast' => self::LEVEL_LAKSANA,
        'blast_campaign' => self::LEVEL_LAKSANA,
        'create_task' => self::LEVEL_LAKSANA,
        'create_event' => self::LEVEL_LAKSANA,
        'update_deal_stage' => self::LEVEL_LAKSANA,

        // Automasi penuh
        'automated_followup' => self::LEVEL_AUTOMASI,
        'send_payment' => self::LEVEL_AUTOMASI,
        'create_order' => self::LEVEL_AUTOMASI,
        'delete_data' => self::LEVEL_AUTOMASI,
        'change_autonomy' => self::LEVEL_AUTOMASI,
        'change_tenant_settings' => self::LEVEL_AUTOMASI,
    ];

    /**
     * Tindakan yang sentiasa memerlukan kelulusan manusia eksplisit,
     * tanpa mengira tahap autonomy.
     *
     * @var string[]
     */
    public const TINDAKAN_SENTIASA_KELULUSAN = [
        'send_payment',
        'delete_data',
        'change_autonomy',
        'change_tenant_settings',
        'send_video_blast',
    ];

    /**
     * Adakah tenant dibenarkan melakukan tindakan ini pada tahap autonomy semasa?
     */
    public function permitted(int $level, string $actionType): bool
    {
        $minimum = self::TINDAKAN_MINIMUM[$actionType] ?? self::LEVEL_LAKSANA;

        return $level >= $minimum;
    }

    /**
     * Adakah tindakan ini memerlukan kelulusan manusia?
     */
    public function requiresApproval(int $level, string $actionType): bool
    {
        // Tindakan sensitif sentiasa perlu approval
        if (in_array($actionType, self::TINDAKAN_SENTIASA_KELULUSAN, true)) {
            return true;
        }

        // Jika tahap autonomy mencukupi → tak perlu approval
        return ! $this->permitted($level, $actionType);
    }

    /**
     * Tahap minimum untuk tindakan — untuk dipaparkan dalam UI.
     */
    public function minimumLevelFor(string $actionType): int
    {
        return self::TINDAKAN_MINIMUM[$actionType] ?? self::LEVEL_LAKSANA;
    }

    /**
     * Label tahap untuk UI.
     */
    public function label(int $level): string
    {
        return match ($level) {
            self::LEVEL_TANYA => 'Tanya',
            self::LEVEL_CADANG => 'Cadang',
            self::LEVEL_DRAF => 'Draf',
            self::LEVEL_LAKSANA => 'Laksana',
            self::LEVEL_AUTOMASI => 'Automasi',
            default => 'Tanya',
        };
    }

    /**
     * Penerangan tahap untuk UI.
     */
    public function description(int $level): string
    {
        return match ($level) {
            self::LEVEL_TANYA => 'Boleh baca dan jawab sahaja. Tiada tindakan tulis.',
            self::LEVEL_CADANG => 'Boleh cadang tindakan. Semua tindakan tetap perlu kelulusan anda.',
            self::LEVEL_DRAF => 'Boleh jana draf (e-mel, WhatsApp, video, proposal). Anda luluskan sebelum dihantar.',
            self::LEVEL_LAKSANA => 'Boleh laksana selepas satu klik kelulusan anda.',
            self::LEVEL_AUTOMASI => 'Boleh automasi dalam had yang ditetapkan. Semua tindakan diaudit.',
            default => 'Boleh baca dan jawab sahaja.',
        };
    }

    /**
     * Semua tahap + metadata untuk UI.
     */
    public function semuaTahap(): array
    {
        return collect(range(self::LEVEL_TANYA, self::LEVEL_AUTOMASI))
            ->map(fn ($level) => [
                'level' => $level,
                'label' => $this->label($level),
                'description' => $this->description($level),
                'examples' => $this->examples($level),
            ])
            ->toArray();
    }

    /**
     * Contoh tindakan bagi setiap tahap.
     */
    private function examples(int $level): array
    {
        return match ($level) {
            self::LEVEL_TANYA => ['Tunjuk jualan bulan ini', 'Berapa bilangan tiket SLA belum selesai?'],
            self::LEVEL_CADANG => ['Saya cadang hantar e-mel follow-up kepada 12 prospek panas'],
            self::LEVEL_DRAF => ['Draf e-mel follow-up', 'Draf video perkenalan untuk prospect A', 'Draf proposal untuk syarikat B'],
            self::LEVEL_LAKSANA => ['Hantar e-mel yang telah diluluskan', 'Kemaskini tahap deal kepada Won'],
            self::LEVEL_AUTOMASI => ['Auto follow-up harian (maks 10)', 'Auto-kemaskini deliverability'],
            default => [],
        };
    }

    /**
     * Senarai tindakan yang perlu kelulusan pada tahap tertentu.
     */
    public function actionsRequiringApproval(int $level): array
    {
        $out = [];

        foreach (array_keys(self::TINDAKAN_MINIMUM) as $action) {
            if ($this->requiresApproval($level, $action)) {
                $out[] = [
                    'action' => $action,
                    'minimum_level' => $this->minimumLevelFor($action),
                ];
            }
        }

        return $out;
    }
}
