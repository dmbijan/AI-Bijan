<?php

namespace App\Filament\Resources\AiUsageEvents\Pages;

use App\Filament\Resources\AiUsageEvents\AiUsageEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAiUsageEvents extends CreateRecord
{
    protected static string $resource = AiUsageEventResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
