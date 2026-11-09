<?php

namespace App\Filament\Resources\PosGiftCards\Pages;

use App\Filament\Resources\PosGiftCards\PosGiftCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosGiftCards extends ListRecords
{
    protected static string $resource = PosGiftCardResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
