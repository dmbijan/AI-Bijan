<?php

namespace App\Filament\Resources\SchedulingSlots\Pages;

use App\Filament\Resources\SchedulingSlots\SchedulingSlotResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedulingSlots extends CreateRecord
{
    protected static string $resource = SchedulingSlotResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
