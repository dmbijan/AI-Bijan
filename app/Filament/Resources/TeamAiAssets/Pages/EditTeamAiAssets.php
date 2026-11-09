<?php

namespace App\Filament\Resources\TeamAiAssets\Pages;

use App\Filament\Resources\TeamAiAssets\TeamAiAssetResource;
use Filament\Resources\Pages\EditRecord;

class EditTeamAiAssets extends EditRecord
{
    protected static string $resource = TeamAiAssetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
