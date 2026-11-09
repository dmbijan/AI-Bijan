<?php

namespace App\Filament\Resources\BillingSubscriptions\Pages;

use App\Filament\Resources\BillingSubscriptions\BillingSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListBillingSubscriptions extends ListRecords
{
    protected static string $resource = BillingSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
