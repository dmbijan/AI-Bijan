<?php

namespace App\Filament\Resources\LoyaltyMembers\Pages;

use App\Filament\Resources\LoyaltyMembers\LoyaltyMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoyaltyMembers extends CreateRecord
{
    protected static string $resource = LoyaltyMemberResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}