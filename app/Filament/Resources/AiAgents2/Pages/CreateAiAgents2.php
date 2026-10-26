<?php

namespace App\Filament\Resources\AiAgents2\Pages;

use App\Filament\Resources\AiAgents2\AiAgentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAiAgents2 extends CreateRecord
{
    protected static string $resource = AiAgentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}