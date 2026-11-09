<?php

namespace App\Filament\Resources\LiveAvatars\Pages;

use App\Filament\Resources\LiveAvatars\LiveAvatarResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveAvatars extends EditRecord
{
    protected static string $resource = LiveAvatarResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
