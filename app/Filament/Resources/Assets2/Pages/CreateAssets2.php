<?php

namespace App\Filament\Resources\Assets2\Pages;

use App\Filament\Resources\Assets2\AssetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssets2 extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}