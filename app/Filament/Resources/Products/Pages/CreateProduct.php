<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Services\ImageService;
use App\Support\SlugGenerator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = SlugGenerator::unique(Product::class, $data['title']);

        $cover = $data['cover_path'] ?? null;

        // Only a fresh upload is stored; anything else means no cover.
        $data['cover_path'] = $cover instanceof UploadedFile
            ? app(ImageService::class)->storeWithSlug(
                slug: $data['slug'],
                file: $cover,
                directory: 'products',
                disk: 'public',
            )
            : null;

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->record->title . ' criado. Agora envie os arquivos.';
    }

    // Straight to the edit page: files and chapters are added there.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
