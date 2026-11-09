<?php

namespace App\Filament\Resources\PosGiftCards\Pages;

use App\Filament\Resources\PosGiftCards\PosGiftCardResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosGiftCards extends CreateRecord
{
    protected static string $resource = PosGiftCardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
