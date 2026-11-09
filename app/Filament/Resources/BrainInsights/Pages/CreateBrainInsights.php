<?php

namespace App\Filament\Resources\BrainInsights\Pages;

use App\Filament\Resources\BrainInsights\InsightResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrainInsights extends CreateRecord
{
    protected static string $resource = InsightResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
