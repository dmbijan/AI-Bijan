<?php

namespace App\Filament\Resources\Boms\Pages;

use App\Filament\Resources\Boms\BomResource;
use Filament\Resources\Pages\EditRecord;

class EditBoms extends EditRecord
{
    protected static string $resource = BomResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
