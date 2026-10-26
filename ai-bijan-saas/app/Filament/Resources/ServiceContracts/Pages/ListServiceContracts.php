<?php

namespace App\Filament\Resources\ServiceContracts\Pages;

use App\Filament\Resources\ServiceContracts\ServiceContractResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceContracts extends ListRecords
{
    protected static string $resource = ServiceContractResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}