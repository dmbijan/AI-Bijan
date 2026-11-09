<?php

namespace App\Filament\Resources\LiveMetrics\Pages;

use App\Filament\Resources\LiveMetrics\LiveMetricResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveMetrics extends CreateRecord
{
    protected static string $resource = LiveMetricResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
