<?php

namespace App\Filament\Resources\AlertRules\Pages;

use App\Filament\Resources\AlertRules\AlertRuleResource;
use Filament\Resources\Pages\EditRecord;

class EditAlertRules extends EditRecord
{
    protected static string $resource = AlertRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
