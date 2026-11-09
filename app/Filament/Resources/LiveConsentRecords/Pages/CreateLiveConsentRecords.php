<?php

namespace App\Filament\Resources\LiveConsentRecords\Pages;

use App\Filament\Resources\LiveConsentRecords\LiveConsentRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveConsentRecords extends CreateRecord
{
    protected static string $resource = LiveConsentRecordResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
