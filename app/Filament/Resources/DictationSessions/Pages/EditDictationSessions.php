<?php

namespace App\Filament\Resources\DictationSessions\Pages;

use App\Filament\Resources\DictationSessions\DictationSessionResource;
use Filament\Resources\Pages\EditRecord;

class EditDictationSessions extends EditRecord
{
    protected static string $resource = DictationSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
