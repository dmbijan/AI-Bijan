<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Resources\Pages\ViewRecord;

class ViewExpenses extends ViewRecord
{
    protected static string $resource = ExpenseResource::class;
}