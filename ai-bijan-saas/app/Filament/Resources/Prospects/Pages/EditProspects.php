<?php

namespace App\Filament\Resources\Prospects\Pages;

use App\Filament\Resources\Prospects\ProspectResource;
use Filament\Resources\Pages\EditRecord;

class EditProspects extends EditRecord
{
    protected static string $resource = ProspectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}