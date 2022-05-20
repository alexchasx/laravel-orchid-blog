<?php

namespace App\Models\Blog;

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
        // Не адекватно работает для статей с active=0
        return $this->belongsToMany(
            Article::class,
            'article_tags',
            // 'tag_id',
            // 'article_id'
        );
    }

    /**
     * Возращает список всех тегов
     *
     * @return Tag[] | Collection
     */
    public static function allActive()
    {
        return self::select('id', 'title', 'active')
            // ->orderBy('title')
            ->where('active', true)
            ->get();

        // self::disableEmpty($tags);
    }

    public static function disableEmpty()
    {
    }
}
