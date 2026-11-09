<?php

namespace App\Filament\Resources\OmniOrders\Pages;

use App\Filament\Resources\OmniOrders\OmniOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOmniOrders extends ListRecords
{
    protected static string $resource = OmniOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
