<?php

namespace App\Filament\Resources\GenAiProviders\Pages;

use App\Filament\Resources\GenAiProviders\GenAiProviderResource;
use Filament\Resources\Pages\EditRecord;

class EditGenAiProviders extends EditRecord
{
    protected static string $resource = GenAiProviderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
