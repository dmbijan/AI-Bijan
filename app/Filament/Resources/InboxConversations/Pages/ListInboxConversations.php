<?php

namespace App\Filament\Resources\InboxConversations\Pages;

use App\Filament\Resources\InboxConversations\InboxConversationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInboxConversations extends ListRecords
{
    protected static string $resource = InboxConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
