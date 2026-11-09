<?php

namespace App\Filament\Resources\CommunitySpaces\Pages;

use App\Filament\Resources\CommunitySpaces\CommunitySpaceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommunitySpaces extends CreateRecord
{
    protected static string $resource = CommunitySpaceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
