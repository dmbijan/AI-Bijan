<?php

namespace App\Filament\Resources\PlanLimits\Pages;

use App\Filament\Resources\PlanLimits\PlanLimitResource;
use Filament\Resources\Pages\EditRecord;

class EditPlanLimits extends EditRecord
{
    protected static string $resource = PlanLimitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
