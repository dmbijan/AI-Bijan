<?php

namespace App\Filament\Resources\Assets2\Pages;

use App\Filament\Resources\Assets2\AssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssets2 extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
