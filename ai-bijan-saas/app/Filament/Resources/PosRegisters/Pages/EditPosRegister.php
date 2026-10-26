<?php

namespace App\Filament\Resources\PosRegisters\Pages;

use App\Filament\Resources\PosRegisters\PosRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPosRegister extends EditRecord
{
    protected static string $resource = PosRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}