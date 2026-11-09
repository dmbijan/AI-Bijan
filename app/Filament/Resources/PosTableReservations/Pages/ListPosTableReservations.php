<?php

namespace App\Filament\Resources\PosTableReservations\Pages;

use App\Filament\Resources\PosTableReservations\PosTableReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosTableReservations extends ListRecords
{
    protected static string $resource = PosTableReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
