<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Services\ImageService;
use App\Traits\CleansRichContentImages;
use App\Traits\HasPublicImages;
use App\Traits\TracksViews;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use CleansRichContentImages;
    use HasPublicImages;
    use SoftDeletes;
    use TracksViews;

    protected $fillable = [
        'type',
        'title',
        'slug',
        'subtitle',
        'summary',
        'body',
        'cover_path',
        'price',
        'compare_at_price',
        'highlights',
        'specs',
        'faq',
        'version',
        'has_reader',
        'seo_title',
        'seo_description',
        'is_published',
        'is_featured',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'type'             => ProductType::class,
        'price'            => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'highlights'       => 'array',
        'specs'            => 'array',
        'faq'              => 'array',
        'has_reader'       => 'boolean',
        'is_published'     => 'boolean',
        'is_featured'      => 'boolean',
        'published_at'     => 'datetime',
        'sort_order'       => 'integer',
    ];

    protected static function booted(): void
    {
        // First publication date, kept when the product is unpublished and published again.
        static::saving(function (Product $product) {
            if ($product->is_published && ! $product->published_at) {
                $product->published_at = now();
            }
        });

        // Purchases protect the row (restrictive FK), so this only runs for
        // products nobody bought. Soft deletes keep the files until a force delete.
        static::forceDeleting(function (Product $product) {
            if ($product->cover_path) {
                app(ImageService::class)->deleteImageAndThumb($product->cover_path, 'public');
            }

            $product->files()->get()->each->delete();
        });
    }

    // ─── Accessors ──────────────────────────────────────────────────

    protected function coverUrl(): Attribute
    {
        return Attribute::make(get: fn () => $this->publicImageUrl($this->cover_path));
    }

    protected function coverThumbUrl(): Attribute
    {
        return Attribute::make(get: fn () => $this->publicThumbUrl($this->cover_path));
    }

    protected function metaTitle(): Attribute
    {
        return Attribute::make(get: fn () => $this->seo_title ?: $this->title);
    }

    protected function metaDescription(): Attribute
    {
        return Attribute::make(get: fn () => $this->seo_description ?: $this->summary);
    }

    /**
     * Public sales page, e.g. /ebooks/concreto-armado.
     */
    protected function url(): Attribute
    {
        return Attribute::make(get: fn () => route('products.show', [
            'type'    => $this->type->segment(),
            'product' => $this->slug,
        ]));
    }

    public function formattedPrice(): string
    {
        return self::money($this->price);
    }

    public function formattedCompareAtPrice(): ?string
    {
        return $this->hasDiscount() ? self::money($this->compare_at_price) : null;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_at_price !== null && (float) $this->compare_at_price > (float) $this->price;
    }

    public function discountPercent(): ?int
    {
        return $this->hasDiscount()
            ? (int) round((1 - (float) $this->price / (float) $this->compare_at_price) * 100)
            : null;
    }

    public static function money(mixed $value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }

    public function isPublished(): bool
    {
        return $this->is_published && ! $this->trashed();
    }

    /**
     * Whether buyers get the online (digital) version.
     */
    public function hasOnlineReader(): bool
    {
        return $this->has_reader && $this->type === ProductType::Ebook;
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class)->orderBy('sort_order')->orderBy('id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(ProductChapter::class)->orderBy('sort_order')->orderBy('id');
    }

    public function previewChapters(): HasMany
    {
        return $this->chapters()->where('is_preview', true);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOfType(Builder $query, ProductType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at')->orderBy('id');
    }
}
