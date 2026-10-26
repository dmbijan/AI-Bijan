<?php

namespace App\Filament\Resources\AdeCoordinations\Pages;

use App\Filament\Resources\AdeCoordinations\AdeCoordinationResource;
use Filament\Resources\Pages\ListRecords;

class ListAdeCoordinations extends ListRecords
{
    protected static string $resource = AdeCoordinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
