<?php

namespace App\Filament\Resources\AiUsageEvents\Pages;

use App\Filament\Resources\AiUsageEvents\AiUsageEventResource;
use Filament\Resources\Pages\EditRecord;

class EditAiUsageEvents extends EditRecord
{
    protected static string $resource = AiUsageEventResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
