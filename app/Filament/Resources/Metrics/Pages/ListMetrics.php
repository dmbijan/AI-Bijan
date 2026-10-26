<?php

namespace App\Filament\Resources\Metrics\Pages;

use App\Filament\Resources\Metrics\MetricResource;
use Filament\Resources\Pages\ListRecords;

class ListMetrics extends ListRecords
{
    protected static string $resource = MetricResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}