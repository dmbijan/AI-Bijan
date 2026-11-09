<?php

namespace App\Filament\Resources\Resellers\Pages;

use App\Filament\Resources\Resellers\ResellerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateResellers extends CreateRecord
{
    protected static string $resource = ResellerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
