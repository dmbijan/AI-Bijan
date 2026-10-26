<?php

namespace App\Filament\Resources\BoardroomSessions\Pages;

use App\Filament\Resources\BoardroomSessions\BoardroomSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListBoardroomSessions extends ListRecords
{
    protected static string $resource = BoardroomSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}