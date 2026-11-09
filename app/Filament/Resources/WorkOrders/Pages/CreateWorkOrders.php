<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkOrders extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
