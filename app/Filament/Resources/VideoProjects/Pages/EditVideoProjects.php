<?php

namespace App\Filament\Resources\VideoProjects\Pages;

use App\Filament\Resources\VideoProjects\VideoProjectResource;
use Filament\Resources\Pages\EditRecord;

class EditVideoProjects extends EditRecord
{
    protected static string $resource = VideoProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
