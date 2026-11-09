<?php

namespace App\Filament\Resources\LiveSupportChannels\Pages;

use App\Filament\Resources\LiveSupportChannels\LiveSupportChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiveSupportChannels extends ListRecords
{
    protected static string $resource = LiveSupportChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
