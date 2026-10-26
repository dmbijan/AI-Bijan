<?php

namespace App\Filament\Resources\Assets2\Pages;

use App\Filament\Resources\Assets2\AssetResource;
use Filament\Resources\Pages\EditRecord;

class EditAssets2 extends EditRecord
{
    protected static string $resource = AssetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}