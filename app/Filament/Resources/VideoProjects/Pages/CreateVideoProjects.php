<?php

namespace App\Filament\Resources\VideoProjects\Pages;

use App\Filament\Resources\VideoProjects\VideoProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVideoProjects extends CreateRecord
{
    protected static string $resource = VideoProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
