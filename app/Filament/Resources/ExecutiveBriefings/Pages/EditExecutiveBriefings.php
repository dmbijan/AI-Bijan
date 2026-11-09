<?php

namespace App\Filament\Resources\ExecutiveBriefings\Pages;

use App\Filament\Resources\ExecutiveBriefings\ExecutiveBriefingResource;
use Filament\Resources\Pages\EditRecord;

class EditExecutiveBriefings extends EditRecord
{
    protected static string $resource = ExecutiveBriefingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
