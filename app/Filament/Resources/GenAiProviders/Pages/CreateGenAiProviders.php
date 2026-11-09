<?php

namespace App\Filament\Resources\GenAiProviders\Pages;

use App\Filament\Resources\GenAiProviders\GenAiProviderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGenAiProviders extends CreateRecord
{
    protected static string $resource = GenAiProviderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
