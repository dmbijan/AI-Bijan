<?php

namespace App\Filament\Resources\Boms\Pages;

use App\Filament\Resources\Boms\BomResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBoms extends CreateRecord
{
    protected static string $resource = BomResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
