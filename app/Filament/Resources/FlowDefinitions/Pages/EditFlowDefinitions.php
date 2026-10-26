<?php

namespace App\Filament\Resources\FlowDefinitions\Pages;

use App\Filament\Resources\FlowDefinitions\FlowDefinitionResource;
use Filament\Resources\Pages\EditRecord;

class EditFlowDefinitions extends EditRecord
{
    protected static string $resource = FlowDefinitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}