<?php

namespace App\Filament\Resources\PlanLimits\Pages;

use App\Filament\Resources\PlanLimits\PlanLimitResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlanLimits extends CreateRecord
{
    protected static string $resource = PlanLimitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
