<?php

namespace App\Filament\Resources\Signatures\Pages;

use App\Filament\Resources\Signatures\SignatureResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSignatures extends CreateRecord
{
    protected static string $resource = SignatureResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
