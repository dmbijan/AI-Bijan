<?php

namespace App\Filament\Resources\OmniChannels\Pages;

use App\Filament\Resources\OmniChannels\OmniChannelResource;
use Filament\Resources\Pages\ListRecords;

class ListOmniChannels extends ListRecords
{
    protected static string $resource = OmniChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}