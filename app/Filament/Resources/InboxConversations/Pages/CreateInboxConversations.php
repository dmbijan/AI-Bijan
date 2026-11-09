<?php

namespace App\Filament\Resources\InboxConversations\Pages;

use App\Filament\Resources\InboxConversations\InboxConversationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInboxConversations extends CreateRecord
{
    protected static string $resource = InboxConversationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
