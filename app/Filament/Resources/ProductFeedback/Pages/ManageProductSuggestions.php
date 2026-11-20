<?php

namespace App\Filament\Resources\ProductFeedback\Pages;

use App\Filament\Resources\ProductFeedback\ProductSuggestionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageProductSuggestions extends ManageRecords
{
    protected static string $resource = ProductSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
