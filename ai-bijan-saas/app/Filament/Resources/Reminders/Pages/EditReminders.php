<?php

namespace App\Filament\Resources\Reminders\Pages;

use App\Filament\Resources\Reminders\ReminderResource;
use Filament\Resources\Pages\EditRecord;

class EditReminders extends EditRecord
{
    protected static string $resource = ReminderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}