<?php

namespace App\Filament\Resources\SharedInboxes\Pages;

use App\Filament\Resources\SharedInboxes\SharedInboxResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSharedInboxes extends ListRecords
{
    protected static string $resource = SharedInboxResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
