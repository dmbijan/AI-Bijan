<?php

namespace App\Filament\Resources\CommunitySpaces\Pages;

use App\Filament\Resources\CommunitySpaces\CommunitySpaceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommunitySpaces extends ListRecords
{
    protected static string $resource = CommunitySpaceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
