<?php

namespace App\Filament\Resources\PersonalTasks\Pages;

use App\Filament\Resources\PersonalTasks\PersonalTaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPersonalTasks extends ListRecords
{
    protected static string $resource = PersonalTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
