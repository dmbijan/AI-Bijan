<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomers extends ViewRecord
{
    protected static string $resource = CustomerResource::class;
}