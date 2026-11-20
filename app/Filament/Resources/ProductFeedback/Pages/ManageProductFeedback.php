<?php

namespace App\Filament\Resources\ProductFeedback\Pages;

use App\Filament\Resources\ProductFeedback\ProductFeedbackResource;
use App\Models\ProductFeedback;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageProductFeedback extends ManageRecords
{
    protected static string $resource = ProductFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Aguardando análise')
                ->badge(ProductFeedback::query()->where('type', ProductFeedback::TYPE_REVIEW)->where('status', ProductFeedback::STATUS_PENDING)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ProductFeedback::STATUS_PENDING)),
            'all' => Tab::make('Todas'),
        ];
    }
}
