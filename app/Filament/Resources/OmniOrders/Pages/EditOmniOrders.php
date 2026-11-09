<?php

namespace App\Filament\Resources\OmniOrders\Pages;

use App\Filament\Resources\OmniOrders\OmniOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditOmniOrders extends EditRecord
{
    protected static string $resource = OmniOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
