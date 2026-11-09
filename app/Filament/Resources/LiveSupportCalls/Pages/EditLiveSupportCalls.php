<?php

namespace App\Filament\Resources\LiveSupportCalls\Pages;

use App\Filament\Resources\LiveSupportCalls\LiveSupportCallResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveSupportCalls extends EditRecord
{
    protected static string $resource = LiveSupportCallResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
