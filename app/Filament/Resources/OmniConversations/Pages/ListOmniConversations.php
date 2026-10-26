<?php

namespace App\Filament\Resources\OmniConversations\Pages;

use App\Filament\Resources\OmniConversations\OmniConversationResource;
use Filament\Resources\Pages\ListRecords;

class ListOmniConversations extends ListRecords
{
    protected static string $resource = OmniConversationResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}