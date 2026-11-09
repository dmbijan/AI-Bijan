<?php

namespace App\Filament\Resources\LiveRenderJobs\Pages;

use App\Filament\Resources\LiveRenderJobs\LiveRenderJobResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveRenderJobs extends CreateRecord
{
    protected static string $resource = LiveRenderJobResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
