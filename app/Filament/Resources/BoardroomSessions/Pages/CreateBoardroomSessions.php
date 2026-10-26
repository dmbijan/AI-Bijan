<?php

namespace App\Filament\Resources\BoardroomSessions\Pages;

use App\Filament\Resources\BoardroomSessions\BoardroomSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBoardroomSessions extends CreateRecord
{
    protected static string $resource = BoardroomSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}