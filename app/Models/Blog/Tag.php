<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Tag extends Model
{
    use HasFactory;
    use AsSource;

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
    ];

    /**
     * Статьи, принадлежащие тегу.
     *
     * @return BelongsToMany
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
    public static function notEmpties()
    {
        return self::select('id', 'title', 'active')
            ->whereHas('articles', function (Builder $builder) {
                $builder = Article::published($builder);
            })->where('active', true)->get();
    }
}
