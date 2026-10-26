<?php

namespace App\Filament\Resources\OmniConversations\Pages;

use App\Filament\Resources\OmniConversations\OmniConversationResource;
use Filament\Resources\Pages\EditRecord;

class EditOmniConversations extends EditRecord
{
    protected static string $resource = OmniConversationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}