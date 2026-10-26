<?php

namespace App\Filament\Resources\CodOrders\Pages;

use App\Filament\Resources\CodOrders\CodOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditCodOrders extends EditRecord
{
    protected static string $resource = CodOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}