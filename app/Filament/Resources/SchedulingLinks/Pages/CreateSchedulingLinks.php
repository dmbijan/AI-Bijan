<?php

namespace App\Filament\Resources\SchedulingLinks\Pages;

use App\Filament\Resources\SchedulingLinks\SchedulingLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedulingLinks extends CreateRecord
{
    protected static string $resource = SchedulingLinkResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
