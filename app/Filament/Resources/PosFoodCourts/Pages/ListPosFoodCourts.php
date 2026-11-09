<?php

namespace App\Filament\Resources\PosFoodCourts\Pages;

use App\Filament\Resources\PosFoodCourts\PosFoodCourtResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosFoodCourts extends ListRecords
{
    protected static string $resource = PosFoodCourtResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
