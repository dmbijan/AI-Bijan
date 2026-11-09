<?php

namespace App\Filament\Resources\CommunityPosts\Pages;

use App\Filament\Resources\CommunityPosts\CommunityPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommunityPosts extends CreateRecord
{
    protected static string $resource = CommunityPostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
