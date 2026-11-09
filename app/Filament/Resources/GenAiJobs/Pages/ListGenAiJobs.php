<?php

namespace App\Filament\Resources\GenAiJobs\Pages;

use App\Filament\Resources\GenAiJobs\GenAiJobResource;
use Filament\Resources\Pages\ListRecords;

class ListGenAiJobs extends ListRecords
{
    protected static string $resource = GenAiJobResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
