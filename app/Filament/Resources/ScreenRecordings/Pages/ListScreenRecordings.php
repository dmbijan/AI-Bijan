<?php

namespace App\Filament\Resources\ScreenRecordings\Pages;

use App\Filament\Resources\ScreenRecordings\ScreenRecordingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScreenRecordings extends ListRecords
{
    protected static string $resource = ScreenRecordingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
