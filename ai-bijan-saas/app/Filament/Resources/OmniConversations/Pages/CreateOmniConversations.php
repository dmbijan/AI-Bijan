<?php

namespace App\Filament\Resources\OmniConversations\Pages;

use App\Filament\Resources\OmniConversations\OmniConversationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOmniConversations extends CreateRecord
{
    protected static string $resource = OmniConversationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}