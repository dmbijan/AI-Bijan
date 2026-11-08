<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\SharedInbox\Models\CannedResponse;
use App\Modules\SharedInbox\Models\InboxConversation;
use App\Modules\SharedInbox\Models\InboxMacro;
use App\Modules\SharedInbox\Models\InboxMessage;
use App\Modules\SharedInbox\Models\SharedInbox;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul SharedInbox (peti masuk bersama).
 *
 * Ditambah 8 Nov 2026. Perbincangan mendedahkan mesej pelanggan
 * (data peribadi) dan sejarah sokongan ejen.
 */
class SharedInboxTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'SharedInbox';

    protected array $model = [
        SharedInbox::class,
        InboxConversation::class,
        InboxMessage::class,
        CannedResponse::class,
        InboxMacro::class,
    ];

    protected ?string $laluanApi = '/api/v1/shared-inbox/inboxes';

    protected ?string $namaRahsia = 'Peti Masuk Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        $inbox = SharedInbox::create([
            'name' => 'Peti Masuk Anak '.$n,
            'slug' => 'peti-masuk-anak-'.$n,
            'channel_type' => 'chat',
            'assignment_strategy' => 'round_robin',
        ]);

        $conversation = InboxConversation::create([
            'shared_inbox_id' => $inbox->id,
            'conversation_no' => 'INB-ANAK-'.$n,
            'contact_name' => 'Pelajar '.$n,
            'status' => InboxConversation::STATUS_OPEN,
        ]);

        switch ($this->rekodSemasa) {
            case SharedInbox::class:
                return [
                    'name' => 'Peti Masuk Rahsia Tenant Satu '.$n,
                    'slug' => 'peti-masuk-rahsia-'.$n,
                    'channel_type' => 'chat',
                    'assignment_strategy' => 'round_robin',
                ];

            case InboxConversation::class:
                return [
                    'shared_inbox_id' => $inbox->id,
                    'conversation_no' => 'INB-RAHSIA-'.$n,
                    'contact_name' => 'Pelajar Rahsia Tenant Satu '.$n,
                    'status' => InboxConversation::STATUS_OPEN,
                ];

            case InboxMessage::class:
                return [
                    'conversation_id' => $conversation->id,
                    'type' => InboxMessage::TYPE_INCOMING,
                    'sender_type' => 'contact',
                    'content' => 'Mesej Rahsia Tenant Satu '.$n,
                ];

            case CannedResponse::class:
                return [
                    'shared_inbox_id' => $inbox->id,
                    'short_code' => 'rahasia'.$n,
                    'title' => 'Ulasan Rahsia Tenant Satu '.$n,
                    'content' => 'Balasan Rahsia Tenant Satu '.$n,
                ];

            default:
                return [
                    'shared_inbox_id' => $inbox->id,
                    'name' => 'Makro Rahsia Tenant Satu '.$n,
                    'actions' => [['type' => 'send_reply', 'content' => 'Rahsia '.$n]],
                ];
        }
    }
}
