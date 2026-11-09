<?php

namespace App\Filament\Resources\LiveAvatars\Pages;

use App\Filament\Resources\LiveAvatars\LiveAvatarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiveAvatars extends ListRecords
{
    protected static string $resource = LiveAvatarResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
