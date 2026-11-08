<?php

namespace App\Modules\SharedInbox\Services;

use App\Modules\SharedInbox\Models\InboxConversation;
use App\Modules\SharedInbox\Models\InboxMacro;
use App\Modules\SharedInbox\Models\InboxMessage;
use App\Modules\SharedInbox\Models\SharedInbox;
use App\Support\Tenancy\TenantContext;

/**
 * SharedInboxService — peti masuk bersama ala Chatwoot.
 *
 * inspired oleh https://github.com/chatwoot/chatwoot
 *
 * Menyediakan:
 *   - peti masuk bersama dengan ejen dan strategi penugasan
 *   - SLA respons & penyelesaian dengan pengesanan lampau
 *   - makro: rangkaian tindakan automatik
 *   - ulasan sedia ada (canned responses)
 *   - penugasan automatik giliran-beratur / beban-rendah
 */
class SharedInboxService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * ID tenant semasa.
     */
    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    /**
     * Cipta perbincangan baharu dalam peti masuk.
     *
     * @param  array<string, mixed>  $data
     */
    public function createConversation(SharedInbox $inbox, array $data): InboxConversation
    {
        $slaResponse = $inbox->sla_response_minutes > 0
            ? now()->addMinutes($inbox->sla_response_minutes)
            : null;

        $slaResolution = $inbox->sla_resolution_minutes > 0
            ? now()->addMinutes($inbox->sla_resolution_minutes)
            : null;

        $conversation = InboxConversation::create([
            'shared_inbox_id' => $inbox->id,
            'conversation_no' => 'INB-'.str_pad((string) (InboxConversation::count() + 1), 6, '0', STR_PAD_LEFT),
            'contact_name' => $data['contact_name'] ?? 'Tetamu',
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'channel' => $data['channel'] ?? $inbox->channel_type,
            'subject' => $data['subject'] ?? null,
            'status' => InboxConversation::STATUS_OPEN,
            'priority' => $data['priority'] ?? 'normal',
            'tags' => $data['tags'] ?? [],
            'sla_response_due_at' => $slaResponse,
            'sla_resolution_due_at' => $slaResolution,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($inbox->auto_assign_enabled) {
            $this->assignConversation($conversation);
        }

        // Mesej pembuka.
        if (! empty($data['content'])) {
            $this->addMessage($conversation, [
                'content' => $data['content'],
                'type' => InboxMessage::TYPE_INCOMING,
                'sender_type' => 'contact',
            ]);
        }

        return $conversation->fresh();
    }

    /**
     * Tambah mesej pada perbincangan.
     *
     * @param  array<string, mixed>  $data
     */
    public function addMessage(InboxConversation $conversation, array $data): InboxMessage
    {
        $mesej = InboxMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $data['sender_id'] ?? null,
            'sender_type' => $data['sender_type'] ?? 'agent',
            'type' => $data['type'] ?? InboxMessage::TYPE_OUTGOING,
            'content' => $data['content'] ?? '',
            'attachments' => $data['attachments'] ?? null,
            'is_ai_generated' => (bool) ($data['is_ai_generated'] ?? false),
            'sent_at' => now(),
        ]);

        $conversation->update([
            'message_count' => $conversation->message_count + 1,
            'last_message_at' => now(),
        ]);

        // Balasan pertama menutup jam SLA respons.
        if ($mesej->type === InboxMessage::TYPE_OUTGOING && $conversation->first_response_at === null) {
            $conversation->update(['first_response_at' => now()]);
        }

        return $mesej;
    }

    /**
     * Tugaskan perbincangan kepada ejen mengikut strategi.
     */
    public function assignConversation(InboxConversation $conversation, ?int $ejenId = null): InboxConversation
    {
        $inbox = $conversation->sharedInbox;

        if ($ejenId !== null) {
            $conversation->update(['assigned_to' => $ejenId]);

            return $conversation->fresh();
        }

        $ejenIds = $this->senaraiEjen($inbox);

        if ($ejenIds === []) {
            return $conversation;
        }

        $strategi = $inbox->assignment_strategy ?: 'round_robin';

        if ($strategi === 'manual') {
            return $conversation;
        }

        if ($strategi === 'least_loaded') {
            $beban = $this->bebanEjen($ejenIds);
            $palingRendah = collect($beban)->sortBy('count')->first();
            $conversation->update(['assigned_to' => $palingRendah['agent_id'] ?? null]);

            return $conversation->fresh();
        }

        // Giliran beratur: ejen dengan perbincangan terbuka paling kurang,
        // dipecah oleh yang terakhir menerima.
        $terakhir = InboxConversation::query()
            ->whereNotNull('assigned_to')
            ->orderByDesc('assigned_to')
            ->orderByDesc('created_at')
            ->first();

        $seterusnya = $ejenIds[0];

        if ($terakhir) {
            $indeks = array_search($terakhir->assigned_to, $ejenIds, true);

            if ($indeks !== false) {
                $seterusnya = $ejenIds[($indeks + 1) % count($ejenIds)];
            }
        }

        $conversation->update(['assigned_to' => $seterusnya]);

        return $conversation->fresh();
    }

    /**
     * Senarai ejen yang boleh menerima tugasan.
     *
     * @return array<int, int>
     */
    protected function senaraiEjen(SharedInbox $inbox): array
    {
        $settings = $inbox->settings ?? [];

        return array_map('intval', $settings['agent_ids'] ?? []);
    }

    /**
     * Beban kerja setiap ejen.
     *
     * @param  array<int, int>  $ejenIds
     * @return array<int, array{agent_id: int, count: int}>
     */
    protected function bebanEjen(array $ejenIds): array
    {
        $jumlah = InboxConversation::query()
            ->whereIn('assigned_to', $ejenIds)
            ->whereIn('status', [InboxConversation::STATUS_OPEN, InboxConversation::STATUS_PENDING])
            ->selectRaw('assigned_to, COUNT(*) as jumlah')
            ->groupBy('assigned_to')
            ->pluck('jumlah', 'assigned_to')
            ->all();

        return array_map(fn ($id) => [
            'agent_id' => $id,
            'count' => (int) ($jumlah[$id] ?? 0),
        ], $ejenIds);
    }

    /**
     * Lakukan makro pada perbincangan.
     *
     * @return array{success: bool, executed: array<int, string>, error: ?string}
     */
    public function runMacro(InboxMacro $macro, InboxConversation $conversation): array
    {
        $dilakukan = [];

        foreach ($macro->actions ?? [] as $tindakan) {
            $jenis = $tindakan['type'] ?? '';

            switch ($jenis) {
                case 'send_reply':
                    $this->addMessage($conversation, [
                        'content' => $tindakan['content'] ?? '',
                        'type' => InboxMessage::TYPE_OUTGOING,
                        'is_ai_generated' => (bool) ($tindakan['is_ai_generated'] ?? false),
                    ]);
                    $dilakukan[] = 'send_reply';
                    break;

                case 'set_status':
                    $conversation->update(['status' => $tindakan['value'] ?? InboxConversation::STATUS_PENDING]);
                    $dilakukan[] = 'set_status';
                    break;

                case 'set_priority':
                    $conversation->update(['priority' => $tindakan['value'] ?? 'normal']);
                    $dilakukan[] = 'set_priority';
                    break;

                case 'assign_agent':
                    $this->assignConversation($conversation, isset($tindakan['value']) ? (int) $tindakan['value'] : null);
                    $dilakukan[] = 'assign_agent';
                    break;

                case 'add_tag':
                    $tag = $tindakan['value'] ?? null;

                    if ($tag) {
                        $tagBaru = array_values(array_unique(array_merge($conversation->tags ?? [], [$tag])));
                        $conversation->update(['tags' => $tagBaru]);
                        $dilakukan[] = 'add_tag';
                    }
                    break;

                case 'remove_tag':
                    $tag = $tindakan['value'] ?? null;
                    $tagBaru = array_values(array_diff($conversation->tags ?? [], [$tag]));
                    $conversation->update(['tags' => $tagBaru]);
                    $dilakukan[] = 'remove_tag';
                    break;

                case 'close_conversation':
                    $conversation->update([
                        'status' => InboxConversation::STATUS_CLOSED,
                        'closed_at' => now(),
                    ]);
                    $dilakukan[] = 'close_conversation';
                    break;

                case 'resolve_sla':
                    $conversation->update([
                        'resolved_at' => now(),
                        'status' => InboxConversation::STATUS_RESOLVED,
                        'sla_resolution_breached' => false,
                    ]);
                    $dilakukan[] = 'resolve_sla';
                    break;

                default:
                    break;
            }
        }

        $macro->increment('usage_count');

        return [
            'success' => true,
            'executed' => $dilakukan,
            'error' => null,
        ];
    }

    /**
     * Semak dan tandakan SLA yang dilampaui.
     *
     * @return array{response_breached: int, resolution_breached: int}
     */
    public function semakSla(): array
    {
        $responseLampau = InboxConversation::query()
            ->where('sla_response_breached', false)
            ->whereNull('first_response_at')
            ->whereNotNull('sla_response_due_at')
            ->where('sla_response_due_at', '<', now())
            ->get();

        foreach ($responseLampau as $c) {
            $c->update(['sla_response_breached' => true]);
        }

        $resolutionLampau = InboxConversation::query()
            ->where('sla_resolution_breached', false)
            ->whereNull('resolved_at')
            ->whereNotNull('sla_resolution_due_at')
            ->where('sla_resolution_due_at', '<', now())
            ->get();

        foreach ($resolutionLampau as $c) {
            $c->update(['sla_resolution_breached' => true]);
        }

        return [
            'response_breached' => $responseLampau->count(),
            'resolution_breached' => $resolutionLampau->count(),
        ];
    }

    /**
     * Statistik untuk papan pemuka.
     *
     * @return array<string, mixed>
     */
    public function statistics(SharedInbox $inbox): array
    {
        $conversation = InboxConversation::query()->where('shared_inbox_id', $inbox->id);

        $dalamTunggu = (clone $conversation)->whereIn('status', [
            InboxConversation::STATUS_OPEN,
            InboxConversation::STATUS_PENDING,
        ])->count();

        $slaLampau = (clone $conversation)->where('sla_response_breached', true)->count();

        $purataBalasan = InboxMessage::query()
            ->whereIn('conversation_id', (clone $conversation)->select('id'))
            ->whereNotNull('sent_at')
            ->count();

        $rating = (float) (clone $conversation)->whereNotNull('rating')->avg('rating');

        return [
            'jumlah_perbincangan' => (clone $conversation)->count(),
            'dalam_tunggu' => $dalamTunggu,
            'sla_lampau' => $slaLampau,
            'jumlah_mesej' => $purataBalasan,
            'purata_rating' => round($rating, 2),
            'peratus_sla' => $dalamTunggu > 0 ? round((($dalamTunggu - $slaLampau) / $dalamTunggu) * 100, 2) : 100.0,
        ];
    }
}
