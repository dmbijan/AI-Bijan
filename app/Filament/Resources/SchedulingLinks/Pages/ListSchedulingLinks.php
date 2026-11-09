<?php

namespace App\Filament\Resources\SchedulingLinks\Pages;

use App\Filament\Resources\SchedulingLinks\SchedulingLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchedulingLinks extends ListRecords
{
    protected static string $resource = SchedulingLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
