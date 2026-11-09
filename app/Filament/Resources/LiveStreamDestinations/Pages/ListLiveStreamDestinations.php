<?php

namespace App\Filament\Resources\LiveStreamDestinations\Pages;

use App\Filament\Resources\LiveStreamDestinations\LiveStreamDestinationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiveStreamDestinations extends ListRecords
{
    protected static string $resource = LiveStreamDestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
