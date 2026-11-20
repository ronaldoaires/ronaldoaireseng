<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFeedback extends Model
{
    public const TYPE_REVIEW = 'review';

    public const TYPE_SUGGESTION = 'suggestion';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $table = 'product_feedback';

    protected $fillable = [
        'product_id',
        'user_id',
        'review_user_id',
        'type',
        'status',
        'display_name',
        'rating',
        'subject',
        'content',
        'approved_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plainContent(): string
    {
        $decoded = html_entity_decode($this->content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $withoutCode = preg_replace('/<\s*(script|style)\b[^>]*>.*?<\/\s*\1\s*>/is', '', $decoded);

        return trim(strip_tags($withoutCode ?? $decoded));
    }

    public function scopeApprovedReviews(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_REVIEW)
            ->where('status', self::STATUS_APPROVED)
            ->whereNotNull('approved_at');
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_REVIEW => 'Avaliação',
            self::TYPE_SUGGESTION => 'Sugestão',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Aguardando análise',
            self::STATUS_APPROVED => 'Aprovado',
            self::STATUS_REJECTED => 'Não aprovado',
        ];
    }
}
