<?php

namespace App\Filament\Resources\Dashboards\Pages;

use App\Filament\Resources\Dashboards\DashboardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDashboards extends CreateRecord
{
    protected static string $resource = DashboardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}