<?php

namespace App\Filament\Resources\DigitalCards\Pages;

use App\Filament\Resources\DigitalCards\DigitalCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDigitalCards extends ListRecords
{
    protected static string $resource = DigitalCardResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
