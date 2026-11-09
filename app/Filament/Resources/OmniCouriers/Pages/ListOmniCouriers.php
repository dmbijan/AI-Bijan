<?php

namespace App\Filament\Resources\OmniCouriers\Pages;

use App\Filament\Resources\OmniCouriers\OmniCourierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOmniCouriers extends ListRecords
{
    protected static string $resource = OmniCourierResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
