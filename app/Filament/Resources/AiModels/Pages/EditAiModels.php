<?php

namespace App\Filament\Resources\AiModels\Pages;

use App\Filament\Resources\AiModels\AiModelResource;
use Filament\Resources\Pages\EditRecord;

class EditAiModels extends EditRecord
{
    protected static string $resource = AiModelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
