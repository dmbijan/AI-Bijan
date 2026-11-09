<?php

namespace App\Filament\Resources\LiveHostPersonalities\Pages;

use App\Filament\Resources\LiveHostPersonalities\LiveHostPersonalityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiveHostPersonalities extends ListRecords
{
    protected static string $resource = LiveHostPersonalityResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
