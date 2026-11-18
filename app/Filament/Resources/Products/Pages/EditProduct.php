<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Services\ImageService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('Ver página')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Product $record) => $record->url, shouldOpenInNewTab: true)
                ->visible(fn (Product $record) => $record->isPublished()),

            // Soft delete: buyers keep access in their library.
            DeleteAction::make()
                ->label('Tirar da loja')
                ->modalDescription('O produto some do site, mas quem já comprou continua com acesso na biblioteca.')
                ->successNotificationTitle(fn (Product $record) => $record->title . ' retirado da loja'),

            RestoreAction::make(),

            // Purchases protect the row (restrictive FK).
            ForceDeleteAction::make()
                ->hidden(fn (Product $record) => $record->purchases()->exists()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $images  = app(ImageService::class);
        $oldPath = $this->record->cover_path;
        $cover   = $data['cover_path'] ?? null;

        // New upload replaces the old files; empty means the cover was
        // removed; a string means the stored path is unchanged.
        if ($cover instanceof UploadedFile) {
            $data['cover_path'] = $images->storeWithSlug(
                slug: $this->record->slug,
                file: $cover,
                directory: 'products',
                disk: 'public',
                oldPath: $oldPath,
            );
        } elseif (blank($cover)) {
            if ($oldPath) {
                $images->deleteImageAndThumb($oldPath, 'public');
            }

            $data['cover_path'] = null;
        } else {
            $data['cover_path'] = $oldPath;
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return $this->record->title . ' atualizado';
    }
}
