<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Возращает статью, владеющую данным тегом
     *
     * @return HasOne
     */
    public function article()
    {
        return $this->hasOne(Article::class, 'id');
    }
}
