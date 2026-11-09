<?php

namespace App\Filament\Resources\LiveSupportCalls\Pages;

use App\Filament\Resources\LiveSupportCalls\LiveSupportCallResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveSupportCalls extends CreateRecord
{
    protected static string $resource = LiveSupportCallResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
