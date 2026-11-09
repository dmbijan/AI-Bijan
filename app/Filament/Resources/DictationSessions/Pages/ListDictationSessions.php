<?php

namespace App\Filament\Resources\DictationSessions\Pages;

use App\Filament\Resources\DictationSessions\DictationSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListDictationSessions extends ListRecords
{
    protected static string $resource = DictationSessionResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
