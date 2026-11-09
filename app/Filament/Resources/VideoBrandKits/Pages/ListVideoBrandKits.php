<?php

namespace App\Filament\Resources\VideoBrandKits\Pages;

use App\Filament\Resources\VideoBrandKits\VideoBrandKitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVideoBrandKits extends ListRecords
{
    protected static string $resource = VideoBrandKitResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
