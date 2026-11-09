<?php

namespace App\Filament\Resources\GenAiProviders\Pages;

use App\Filament\Resources\GenAiProviders\GenAiProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGenAiProviders extends ListRecords
{
    protected static string $resource = GenAiProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
