<?php

namespace App\Filament\Resources\PovMetrics\Pages;

use App\Filament\Resources\PovMetrics\PovMetricResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePovMetrics extends CreateRecord
{
    protected static string $resource = PovMetricResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
