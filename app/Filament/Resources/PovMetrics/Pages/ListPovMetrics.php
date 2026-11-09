<?php

namespace App\Filament\Resources\PovMetrics\Pages;

use App\Filament\Resources\PovMetrics\PovMetricResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPovMetrics extends ListRecords
{
    protected static string $resource = PovMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
