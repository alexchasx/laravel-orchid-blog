<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ConsentLog
 *
 * @property int $id
 * @property int $comment_id
 * @property string $consent_type // 'processing' | 'distribution'
 * @property string $consent_text
 * @property string $consent_version
 * @property string $ip_address
 * @property string $user_agent
 * @property string $page_url
 * @property \Illuminate\Support\Carbon $consented_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Comment $comment
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog active()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereConsentText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereConsentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereConsentVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereConsentedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog wherePageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereRevokedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConsentLog extends Model
{
    use HasFactory;

    public const TYPE_PROCESSING = 'processing';
    public const TYPE_DISTRIBUTION = 'distribution';

    protected $fillable = [
        'comment_id',
        'consent_type',
        'consent_text',
        'consent_version',
        'ip_address',
        'user_agent',
        'page_url',
        'consented_at',
        'revoked_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Связь с комментарием.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Скор: только активные согласия (не отозванные).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Отзывает согласие — устанавливает revoked_at.
     */
    public function revoke(): bool
    {
        $this->revoked_at = now();

        return $this->save();
    }
}
