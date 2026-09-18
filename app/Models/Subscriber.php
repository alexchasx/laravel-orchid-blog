<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

/**
 * Подписчик рассылки о новых статьях.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $email
 * @property string|null $token
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Subscriber extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public $fillable = [
        'user_id',
        'email',
        'token',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Автогенерация токена отписки и статуса при создании подписчика.
     */
    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber): void {
            if (empty($subscriber->token)) {
                $subscriber->token = Str::random(64);
            }

            if (empty($subscriber->status)) {
                $subscriber->status = self::STATUS_ACTIVE;
            }
        });
    }

    /**
     * Активные подписчики (не отписавшиеся).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
