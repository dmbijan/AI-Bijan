<?php

namespace App\Filament\Resources\MediaProjects\Pages;

use App\Filament\Resources\MediaProjects\MediaProjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMediaProjects extends ListRecords
{
    protected static string $resource = MediaProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
