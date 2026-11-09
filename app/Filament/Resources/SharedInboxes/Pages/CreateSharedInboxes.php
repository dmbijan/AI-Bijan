<?php

namespace App\Filament\Resources\SharedInboxes\Pages;

use App\Filament\Resources\SharedInboxes\SharedInboxResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSharedInboxes extends CreateRecord
{
    protected static string $resource = SharedInboxResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
