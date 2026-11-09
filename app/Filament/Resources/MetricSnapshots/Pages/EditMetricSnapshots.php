<?php

namespace App\Filament\Resources\MetricSnapshots\Pages;

use App\Filament\Resources\MetricSnapshots\MetricSnapshotResource;
use Filament\Resources\Pages\EditRecord;

class EditMetricSnapshots extends EditRecord
{
    protected static string $resource = MetricSnapshotResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
