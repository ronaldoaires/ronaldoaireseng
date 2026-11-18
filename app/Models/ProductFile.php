<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * A file the buyer downloads. Stored on the private disk and only served
 * through account.files.download, which checks the purchase first.
 */
class ProductFile extends Model
{
    protected $fillable = [
        'product_id',
        'label',
        'disk',
        'path',
        'original_name',
        'size_bytes',
        'version',
        'sort_order',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // The admin upload only gives the path: size comes from the disk.
        static::saving(function (ProductFile $file) {
            if ($file->isDirty('path') && $file->path && Storage::disk($file->disk)->exists($file->path)) {
                $file->size_bytes = Storage::disk($file->disk)->size($file->path);
            }
        });

        // A replaced file must not stay behind on the private disk.
        static::updated(function (ProductFile $file) {
            $old = $file->getOriginal('path');

            if ($file->wasChanged('path') && $old && Storage::disk($file->disk)->exists($old)) {
                Storage::disk($file->disk)->delete($old);
            }
        });

        static::deleted(function (ProductFile $file) {
            if ($file->path && Storage::disk($file->disk)->exists($file->path)) {
                Storage::disk($file->disk)->delete($file->path);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ProductDownload::class);
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->original_name ?: $this->path, PATHINFO_EXTENSION));
    }

    /**
     * Heroicons outline name that matches the file type.
     */
    public function iconName(): string
    {
        return match ($this->extension()) {
            'pdf'                         => 'document-text',
            'xlsx', 'xlsm', 'xls', 'csv' => 'table-cells',
            'zip', 'rar', '7z'           => 'archive-box',
            default                       => 'document',
        };
    }

    public function formattedSize(): string
    {
        $bytes = $this->size_bytes;

        return match (true) {
            $bytes >= 1_048_576 => number_format($bytes / 1_048_576, 1, ',', '.') . ' MB',
            $bytes >= 1024      => number_format($bytes / 1024, 0, ',', '.') . ' KB',
            default             => $bytes . ' B',
        };
    }
}
