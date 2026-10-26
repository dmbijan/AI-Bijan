<?php

namespace App\Filament\Resources\ServiceContracts\Pages;

use App\Filament\Resources\ServiceContracts\ServiceContractResource;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceContracts extends ViewRecord
{
    protected static string $resource = ServiceContractResource::class;
}