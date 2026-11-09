<?php

namespace App\Filament\Resources\BrainForecasts\Pages;

use App\Filament\Resources\BrainForecasts\ForecastResource;
use Filament\Resources\Pages\EditRecord;

class EditBrainForecasts extends EditRecord
{
    protected static string $resource = ForecastResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
