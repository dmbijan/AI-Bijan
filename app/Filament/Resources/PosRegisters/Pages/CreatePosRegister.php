<?php

namespace App\Filament\Resources\PosRegisters\Pages;

use App\Filament\Resources\PosRegisters\PosRegisterResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosRegister extends CreateRecord
{
    protected static string $resource = PosRegisterResource::class;

    /**
     * ⚠️ `pos_registers.code` dan `created_by` ialah NOT NULL.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['code'] ?? null)) {
            $data['code'] = 'POS-'.strtoupper(substr(md5(uniqid('', true)), 0, 6));
        }

        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}