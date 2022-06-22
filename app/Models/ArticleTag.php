<?php

namespace App\Models;

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
     * Возращает статьи, владеющие данным тегом
     *
     * @return HasOne
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'id');
    }
}
