<?php

namespace App\Filament\Resources\PosFoodCourts\Pages;

use App\Filament\Resources\PosFoodCourts\PosFoodCourtResource;
use Filament\Resources\Pages\EditRecord;

class EditPosFoodCourts extends EditRecord
{
    protected static string $resource = PosFoodCourtResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
