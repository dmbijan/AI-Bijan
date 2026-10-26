<?php

namespace App\Modules\Helpdesk;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use App\Services\AuditLogger;
use App\Support\NumberSeries;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Tiket & SLA. Masa SLA dikira ikut jam kalendar (24/7); waktu perniagaan/cuti umum belum diambil kira. */
class HelpdeskService
{
    public const SLA_LALAI = ['kritikal' => [30, 240], 'tinggi' => [60, 480], 'sederhana' => [240, 1440], 'rendah' => [480, 4320]];

    public function sla(string $priority): array
    {
        $p = SlaPolicy::where('priority', $priority)->first();

        return $p ? [$p->first_response_minutes, $p->resolution_minutes] : self::SLA_LALAI[$priority];
    }

    /** Cipta tiket. Pulangkan [tiket, token portal pelanggan (dipapar sekali)]. */
    public function create(array $d): array
    {
        [$resp, $res] = $this->sla($d['priority'] ?? 'sederhana');
        $token = Str::random(48);
        $kat = ! empty($d['category_id']) ? TicketCategory::find($d['category_id']) : null;
        $t = Ticket::create(['number' => NumberSeries::next('ticket', 'TK'), 'subject' => $d['subject'], 'description' => $d['description'], 'requester_name' => $d['requester_name'],
            'requester_email' => $d['requester_email'] ?? null, 'requester_phone' => $d['requester_phone'] ?? null, 'channel' => $d['channel'] ?? 'web', 'category_id' => $kat?->id,
            'priority' => $d['priority'] ?? 'sederhana', 'status' => 'baru', 'assignee_id' => $d['assignee_id'] ?? $kat?->default_assignee_id,
            'first_response_due' => now()->addMinutes($resp), 'resolution_due' => now()->addMinutes($res), 'access_token_hash' => hash('sha256', $token)]);
        app(AuditLogger::class)->log('helpdesk.ticket_created', $t, null, ['number' => $t->number, 'priority' => $t->priority, 'channel' => $t->channel]);

        return [$t, $token];
    }

    /** Balasan. Nota dalaman tidak dilihat pelanggan dan tidak dikira respons pertama. */
    public function reply(Ticket $t, string $body, ?int $userId, bool $internal = false): TicketMessage
    {
        if ($t->status === 'ditutup') {
            throw ValidationException::withMessages(['status' => 'Tiket telah ditutup.']);
        }
        $m = TicketMessage::create(['ticket_id' => $t->id, 'user_id' => $userId, 'internal' => $internal, 'body' => $body]);
        if ($userId && ! $internal) {
            $t->first_responded_at ??= now();
            if ($t->first_responded_at->gt($t->first_response_due)) {
                $t->response_breached = true;
            }
            $t->status = 'menunggu_pelanggan';
        } elseif (! $userId) {
            $t->status = in_array($t->status, ['selesai', 'menunggu_pelanggan'], true) ? 'dibuka' : $t->status; // pelanggan balas → buka semula
            $t->resolved_at = null;
        }
        $t->save();

        return $m;
    }

    public function setStatus(Ticket $t, string $status): Ticket
    {
        $before = ['status' => $t->status];
        $t->status = $status;
        if ($status === 'selesai') {
            $t->resolved_at = now();
            $t->resolution_breached = $t->resolution_breached || now()->gt($t->resolution_due);
        }
        $t->save();
        app(AuditLogger::class)->log('helpdesk.ticket_status', $t, $before, ['status' => $status]);

        return $t;
    }

    /** Tukar keutamaan → kira semula tarikh SLA dari masa tiket dibuat. */
    public function setPriority(Ticket $t, string $priority): Ticket
    {
        [$resp, $res] = $this->sla($priority);
        $t->update(['priority' => $priority, 'first_response_due' => $t->created_at->copy()->addMinutes($resp), 'resolution_due' => $t->created_at->copy()->addMinutes($res)]);

        return $t;
    }

    /** Penjadual: tanda langgar SLA & naikkan tahap eskalasi. Pulangkan bilangan tiket dieskalasi. */
    public function checkSla(): int
    {
        $n = 0;
        $aktif = Ticket::whereNotIn('status', ['selesai', 'ditutup'])->get();
        foreach ($aktif as $t) {
            $ubah = false;
            if (! $t->first_responded_at && now()->gt($t->first_response_due) && ! $t->response_breached) {
                $t->response_breached = true;
                $ubah = true;
            }
            if (now()->gt($t->resolution_due) && ! $t->resolution_breached) {
                $t->resolution_breached = true;
                $ubah = true;
            }
            if ($ubah) {
                $t->escalation_level = min($t->escalation_level + 1, 3);
                $t->save();
                app(AuditLogger::class)->log('helpdesk.sla_breached', $t, null, ['number' => $t->number, 'level' => $t->escalation_level]);
                $n++;
            }
        }

        return $n;
    }
}
