<?php

namespace App\Filament\Resources\CommunityPosts\Pages;

use App\Filament\Resources\CommunityPosts\CommunityPostResource;
use Filament\Resources\Pages\EditRecord;

class EditCommunityPosts extends EditRecord
{
    protected static string $resource = CommunityPostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
