<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ArticleTag
 *
 * @property int $id
 * @property int $article_id
 * @property int $tag_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Article[] $articles
 * @property-read int|null $articles_count
 * @method static \Database\Factories\ArticleTagFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTag whereArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTag whereTagId($value)
 * @mixin \Eloquent
 */
class ArticleTag extends Model
{
    use HasFactory;
    /**
     * Определяет необходимость отметок времени для модели.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Атрибуты, для которых запрещено массовое назначение.
     *
     * @var array
     */
    protected $fillable = [
        'article_id',
        'tag_id',
    ];

    /**
     * Возращает статьи, владеющие данным тегом
     *
     * @return HasOne
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'id');
    }
}
