<?php

namespace App\Filament\Resources\ScreenRecordings\Pages;

use App\Filament\Resources\ScreenRecordings\ScreenRecordingResource;
use Filament\Resources\Pages\EditRecord;

class EditScreenRecordings extends EditRecord
{
    protected static string $resource = ScreenRecordingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
