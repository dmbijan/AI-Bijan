<?php

namespace App\Filament\Resources\ProjectTimelines\Pages;

use App\Filament\Resources\ProjectTimelines\ProjectTimelineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectTimelines extends ListRecords
{
    protected static string $resource = ProjectTimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
