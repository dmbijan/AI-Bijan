<?php

namespace App\Filament\Resources\GenAiJobs\Pages;

use App\Filament\Resources\GenAiJobs\GenAiJobResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGenAiJobs extends CreateRecord
{
    protected static string $resource = GenAiJobResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
