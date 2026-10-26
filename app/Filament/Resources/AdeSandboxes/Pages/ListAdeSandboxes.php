<?php

namespace App\Filament\Resources\AdeSandboxes\Pages;

use App\Filament\Resources\AdeSandboxes\AdeSandboxResource;
use Filament\Resources\Pages\ListRecords;

class ListAdeSandboxes extends ListRecords
{
    protected static string $resource = AdeSandboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
