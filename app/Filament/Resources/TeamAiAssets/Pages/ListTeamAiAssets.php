<?php

namespace App\Filament\Resources\TeamAiAssets\Pages;

use App\Filament\Resources\TeamAiAssets\TeamAiAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeamAiAssets extends ListRecords
{
    protected static string $resource = TeamAiAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
