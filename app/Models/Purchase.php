<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    public const SOURCE_CHECKOUT = 'checkout';

    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'user_id',
        'product_id',
        'amount',
        'licensed_name',
        'status',
        'source',
        'payment_id',
        'payment_url',
        'payment_method',
        'paid_at',
        'notes',
        'last_chapter_id',
        'last_read_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'last_read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        // A product taken off the store stays in the buyer's library.
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function lastChapter(): BelongsTo
    {
        return $this->belongsTo(ProductChapter::class, 'last_chapter_id');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid')->whereNotNull('paid_at');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' && $this->paid_at !== null;
    }

    public function isManual(): bool
    {
        return $this->source === self::SOURCE_MANUAL;
    }

    public static function statusOptions(): array
    {
        return [
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'refunded' => 'Estornado',
            'failed' => 'Falhou',
            'cancelled' => 'Cancelado',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Tone of the status badge in the account area (see x-ui.badge).
     */
    public function statusTone(): string
    {
        return match ($this->status) {
            'paid' => 'success',
            'pending' => 'warning',
            'refunded' => 'info',
            'failed' => 'danger',
            default => 'neutral',
        };
    }

    public function paymentMethodLabel(): string
    {
        if ($this->isManual()) {
            return 'Liberado pelo autor';
        }

        return match ($this->payment_method) {
            'credit_card' => 'Cartão de crédito',
            'debit_card' => 'Cartão de débito',
            'pix' => 'Pix',
            default => $this->payment_method ?? '—',
        };
    }
}
