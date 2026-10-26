<?php

namespace App\Filament\Resources\AiAgents2\Pages;

use App\Filament\Resources\AiAgents2\AiAgentResource;
use Filament\Resources\Pages\EditRecord;

class EditAiAgents2 extends EditRecord
{
    protected static string $resource = AiAgentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}