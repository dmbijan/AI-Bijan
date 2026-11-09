<?php

namespace App\Filament\Resources\BillingSubscriptions\Pages;

use App\Filament\Resources\BillingSubscriptions\BillingSubscriptionResource;
use Filament\Resources\Pages\EditRecord;

class EditBillingSubscriptions extends EditRecord
{
    protected static string $resource = BillingSubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
