<?php

namespace App\Filament\Resources\LiveControls\Pages;

use App\Filament\Resources\LiveControls\LiveControlResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveControls extends EditRecord
{
    protected static string $resource = LiveControlResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
