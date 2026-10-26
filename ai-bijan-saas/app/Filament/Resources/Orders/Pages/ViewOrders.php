<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewOrders extends ViewRecord
{
    protected static string $resource = OrderResource::class;
}