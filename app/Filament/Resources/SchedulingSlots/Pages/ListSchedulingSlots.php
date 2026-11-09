<?php

namespace App\Filament\Resources\SchedulingSlots\Pages;

use App\Filament\Resources\SchedulingSlots\SchedulingSlotResource;
use Filament\Resources\Pages\ListRecords;

class ListSchedulingSlots extends ListRecords
{
    protected static string $resource = SchedulingSlotResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
