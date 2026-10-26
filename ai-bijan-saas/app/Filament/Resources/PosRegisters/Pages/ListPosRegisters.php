<?php

namespace App\Filament\Resources\PosRegisters\Pages;

use App\Filament\Resources\PosRegisters\PosRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPosRegisters extends ListRecords
{
    protected static string $resource = PosRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}