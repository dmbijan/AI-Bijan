<?php

namespace App\Filament\Resources\LiveRenderJobs\Pages;

use App\Filament\Resources\LiveRenderJobs\LiveRenderJobResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveRenderJobs extends EditRecord
{
    protected static string $resource = LiveRenderJobResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
