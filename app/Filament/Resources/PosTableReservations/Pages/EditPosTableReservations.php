<?php

namespace App\Filament\Resources\PosTableReservations\Pages;

use App\Filament\Resources\PosTableReservations\PosTableReservationResource;
use Filament\Resources\Pages\EditRecord;

class EditPosTableReservations extends EditRecord
{
    protected static string $resource = PosTableReservationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
