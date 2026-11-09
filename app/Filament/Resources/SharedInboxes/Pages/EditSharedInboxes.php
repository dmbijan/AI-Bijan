<?php

namespace App\Filament\Resources\SharedInboxes\Pages;

use App\Filament\Resources\SharedInboxes\SharedInboxResource;
use Filament\Resources\Pages\EditRecord;

class EditSharedInboxes extends EditRecord
{
    protected static string $resource = SharedInboxResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
