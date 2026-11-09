<?php

namespace App\Filament\Resources\LiveMetrics\Pages;

use App\Filament\Resources\LiveMetrics\LiveMetricResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveMetrics extends EditRecord
{
    protected static string $resource = LiveMetricResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
