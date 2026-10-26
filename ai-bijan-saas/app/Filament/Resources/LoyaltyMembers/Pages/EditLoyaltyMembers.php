<?php

namespace App\Filament\Resources\LoyaltyMembers\Pages;

use App\Filament\Resources\LoyaltyMembers\LoyaltyMemberResource;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyMembers extends EditRecord
{
    protected static string $resource = LoyaltyMemberResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}