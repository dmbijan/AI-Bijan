<?php

namespace App\Filament\Resources\CostComponents\Pages;

use App\Filament\Resources\CostComponents\CostComponentResource;
use Filament\Resources\Pages\EditRecord;

class EditCostComponents extends EditRecord
{
    protected static string $resource = CostComponentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
