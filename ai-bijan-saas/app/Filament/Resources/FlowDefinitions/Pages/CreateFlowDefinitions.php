<?php

namespace App\Filament\Resources\FlowDefinitions\Pages;

use App\Filament\Resources\FlowDefinitions\FlowDefinitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFlowDefinitions extends CreateRecord
{
    protected static string $resource = FlowDefinitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}