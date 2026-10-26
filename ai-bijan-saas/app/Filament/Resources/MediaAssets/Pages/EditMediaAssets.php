<?php

namespace App\Filament\Resources\MediaAssets\Pages;

use App\Filament\Resources\MediaAssets\MediaAssetResource;
use Filament\Resources\Pages\EditRecord;

class EditMediaAssets extends EditRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}