<?php

namespace App\Filament\Resources\PlanLimits\Pages;

use App\Filament\Resources\PlanLimits\PlanLimitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlanLimits extends ListRecords
{
    protected static string $resource = PlanLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
