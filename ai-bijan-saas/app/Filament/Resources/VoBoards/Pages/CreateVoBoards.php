<?php

namespace App\Filament\Resources\VoBoards\Pages;

use App\Filament\Resources\VoBoards\VoBoardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVoBoards extends CreateRecord
{
    protected static string $resource = VoBoardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}