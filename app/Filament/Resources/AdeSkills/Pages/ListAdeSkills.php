<?php

namespace App\Filament\Resources\AdeSkills\Pages;

use App\Filament\Resources\AdeSkills\AdeSkillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdeSkills extends ListRecords
{
    protected static string $resource = AdeSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
