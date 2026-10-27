<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Reminder\Models\Reminder;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Reminder (peringatan).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Peringatan membawa nota tindakan dan pautan
 * subjek (invois, tiket) — kebocoran bermakna tenant lain boleh membaca
 * kerja tertunggak dan nama pelanggan pesaing.
 */
class ReminderTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Reminder';

    protected array $model = [
        Reminder::class,
    ];

    protected ?string $laluanApi = '/api/v1/reminders';

    protected ?string $namaRahsia = 'Peringatan Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'type_key' => 'custom',
            'title' => 'Peringatan Rahsia Tenant Satu ' . $n,
            'status' => 'pending',
            'priority' => 'normal',
            'due_at' => '2026-11-05 09:00:00',
            'assigned_to' => $this->u->id,
        ];
    }
}
