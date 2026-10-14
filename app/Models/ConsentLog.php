<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\ConsentLog
 *
 * @property int $id
 * @property int|null $comment_id
 * @property string|null $subject_type
 * @property int|null $subject_id
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
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $subject
 * @property-read \App\Models\Comment|null $comment
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog active()
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog forSubject(string $type, int $id)
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
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConsentLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConsentLog extends Model
{
    use HasFactory;

    public const TYPE_PROCESSING = 'processing';
    public const TYPE_DISTRIBUTION = 'distribution';

    public const SUBJECT_COMMENT = 'comment';
    public const SUBJECT_SUBSCRIBER = 'subscriber';
    public const SUBJECT_CONTACT = 'contact';

    protected $fillable = [
        'comment_id',
        'subject_type',
        'subject_id',
        'consent_type',
        'consent_text',
        'consent_version',
        'ip_address',
        'user_agent',
        'page_url',
        'consented_at',
        'revoked_at',
        'processed_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'revoked_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Полиморфная связь: комментарий или подписчик.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Связь с комментарием (для обратной совместимости).
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
     * Скор: фильтровать логи по типу и ID субъекта.
     */
    public function scopeForSubject($query, string $type, int $id)
    {
        return $query->where('subject_type', $type)
            ->where('subject_id', $id);
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
