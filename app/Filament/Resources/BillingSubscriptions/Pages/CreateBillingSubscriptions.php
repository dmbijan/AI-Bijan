<?php

namespace App\Filament\Resources\BillingSubscriptions\Pages;

use App\Filament\Resources\BillingSubscriptions\BillingSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBillingSubscriptions extends CreateRecord
{
    protected static string $resource = BillingSubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
