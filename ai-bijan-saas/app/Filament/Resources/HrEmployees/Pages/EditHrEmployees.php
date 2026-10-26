<?php

namespace App\Filament\Resources\HrEmployees\Pages;

use App\Filament\Resources\HrEmployees\HrEmployeeResource;
use Filament\Resources\Pages\EditRecord;

class EditHrEmployees extends EditRecord
{
    protected static string $resource = HrEmployeeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}