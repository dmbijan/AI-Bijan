<?php

namespace App\Filament\Resources\MetricSnapshots\Pages;

use App\Filament\Resources\MetricSnapshots\MetricSnapshotResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMetricSnapshots extends CreateRecord
{
    protected static string $resource = MetricSnapshotResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
