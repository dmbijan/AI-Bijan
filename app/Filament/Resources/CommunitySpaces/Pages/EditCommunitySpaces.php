<?php

namespace App\Filament\Resources\CommunitySpaces\Pages;

use App\Filament\Resources\CommunitySpaces\CommunitySpaceResource;
use Filament\Resources\Pages\EditRecord;

class EditCommunitySpaces extends EditRecord
{
    protected static string $resource = CommunitySpaceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
