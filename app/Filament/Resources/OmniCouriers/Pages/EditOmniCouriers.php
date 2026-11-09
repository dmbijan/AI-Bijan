<?php

namespace App\Filament\Resources\OmniCouriers\Pages;

use App\Filament\Resources\OmniCouriers\OmniCourierResource;
use Filament\Resources\Pages\EditRecord;

class EditOmniCouriers extends EditRecord
{
    protected static string $resource = OmniCourierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
