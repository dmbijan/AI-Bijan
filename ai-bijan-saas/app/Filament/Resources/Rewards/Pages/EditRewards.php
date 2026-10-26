<?php

namespace App\Filament\Resources\Rewards\Pages;

use App\Filament\Resources\Rewards\RewardResource;
use Filament\Resources\Pages\EditRecord;

class EditRewards extends EditRecord
{
    protected static string $resource = RewardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}