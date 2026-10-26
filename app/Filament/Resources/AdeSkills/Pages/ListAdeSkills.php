<?php

namespace App\Filament\Resources\AdeSkills\Pages;

use App\Filament\Resources\AdeSkills\AdeSkillResource;
use Filament\Resources\Pages\ListRecords;

class ListAdeSkills extends ListRecords
{
    protected static string $resource = AdeSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
