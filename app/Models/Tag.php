<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\AsSource;

class Tag extends Model
{
    use HasFactory;
    use AsSource;
    use SoftDeletes;

    public const SIDEBAR_CACHE_KEY = 'sidebar-tags';

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
        'title',
        'popular',
        'active',
        'count_articles',
    ];

    /**
     * Статьи, принадлежащие тегу.
     */
    public function articles()
    {
        return $this->belongsToMany(
            Article::class,
            'article_tags',
            // 'tag_id',
            // 'article_id'
        );
    }

    public function article_tags()
    {
        return $this->hasMany(ArticleTag::class, 'tag_id');
    }

    /**
     * Возращает список всех тегов
     *
     * @return Tag[] | Collection
     */
    public function scopeArticlePublished($query)
    {
        return $query->addSelect('id', 'title', 'active', 'popular', 'count_articles')
            ->whereHas('articles', function (Builder $builder) {
                $builder = Article::published($builder);
            })->where('active', true);
    }

    public static function updateCountArticles(): void
    {
        foreach (self::all('id', 'count_articles') as $tag) {
            $countPublicArticles = $tag->articles()
                ->where('is_published', true)
                ->count();

            if ($countPublicArticles !== $tag->count_articles) {
                $tag->count_articles = $countPublicArticles;
                $tag->save();
            }
        }
    }
}
