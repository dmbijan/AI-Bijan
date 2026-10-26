<?php

namespace App\Filament\Resources\VoBoards\Pages;

use App\Filament\Resources\VoBoards\VoBoardResource;
use Filament\Resources\Pages\ListRecords;

class ListVoBoards extends ListRecords
{
    protected static string $resource = VoBoardResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}