<?php

namespace App\Filament\Resources\LiveRenderJobs\Pages;

use App\Filament\Resources\LiveRenderJobs\LiveRenderJobResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveRenderJobs extends ListRecords
{
    protected static string $resource = LiveRenderJobResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
