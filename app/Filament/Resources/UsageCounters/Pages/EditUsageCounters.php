<?php

namespace App\Filament\Resources\UsageCounters\Pages;

use App\Filament\Resources\UsageCounters\UsageCounterResource;
use Filament\Resources\Pages\EditRecord;

class EditUsageCounters extends EditRecord
{
    protected static string $resource = UsageCounterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
