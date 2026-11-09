<?php

namespace App\Filament\Resources\SocialPosts\Pages;

use App\Filament\Resources\SocialPosts\SocialPostResource;
use Filament\Resources\Pages\EditRecord;

class EditSocialPosts extends EditRecord
{
    protected static string $resource = SocialPostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
