<?php

namespace App\Filament\Resources\DictationVocabularies\Pages;

use App\Filament\Resources\DictationVocabularies\DictationVocabularyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDictationVocabularies extends ListRecords
{
    protected static string $resource = DictationVocabularyResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
