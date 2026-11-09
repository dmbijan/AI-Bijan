<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrders extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
