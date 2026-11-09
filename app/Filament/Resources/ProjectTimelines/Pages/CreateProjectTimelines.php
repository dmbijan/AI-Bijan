<?php

namespace App\Filament\Resources\ProjectTimelines\Pages;

use App\Filament\Resources\ProjectTimelines\ProjectTimelineResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProjectTimelines extends CreateRecord
{
    protected static string $resource = ProjectTimelineResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
