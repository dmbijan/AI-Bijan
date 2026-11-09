<?php

namespace App\Filament\Resources\DictationVocabularies\Pages;

use App\Filament\Resources\DictationVocabularies\DictationVocabularyResource;
use Filament\Resources\Pages\EditRecord;

class EditDictationVocabularies extends EditRecord
{
    protected static string $resource = DictationVocabularyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
