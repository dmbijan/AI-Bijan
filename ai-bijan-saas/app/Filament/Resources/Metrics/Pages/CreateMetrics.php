<?php

namespace App\Filament\Resources\Metrics\Pages;

use App\Filament\Resources\Metrics\MetricResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMetrics extends CreateRecord
{
    protected static string $resource = MetricResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}