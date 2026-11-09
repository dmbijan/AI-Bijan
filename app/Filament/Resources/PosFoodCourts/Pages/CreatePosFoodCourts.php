<?php

namespace App\Filament\Resources\PosFoodCourts\Pages;

use App\Filament\Resources\PosFoodCourts\PosFoodCourtResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosFoodCourts extends CreateRecord
{
    protected static string $resource = PosFoodCourtResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
