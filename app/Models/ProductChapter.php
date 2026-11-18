<?php

namespace App\Models;

use App\Traits\CleansRichContentImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One chapter of the online (digital) version of an ebook.
 */
class ProductChapter extends Model
{
    use CleansRichContentImages;

    protected $fillable = [
        'product_id',
        'title',
        'slug',
        'body',
        'is_preview',
        'sort_order',
    ];

    protected $casts = [
        'is_preview' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Rough reading time, for the table of contents (about 200 words per minute).
     */
    public function readingMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->body));

        return max(1, (int) ceil($words / 200));
    }
}
