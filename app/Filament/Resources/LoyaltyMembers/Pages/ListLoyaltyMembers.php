<?php

namespace App\Filament\Resources\LoyaltyMembers\Pages;

use App\Filament\Resources\LoyaltyMembers\LoyaltyMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyMembers extends ListRecords
{
    protected static string $resource = LoyaltyMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
