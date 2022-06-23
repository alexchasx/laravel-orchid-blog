<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;

class Rubric extends Model
{
    use HasFactory;
    use AsSource;
    use SoftDeletes;

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
        'parent_id',
        'slug',
        'title',
        'description',
    ];

    /**
     * Возращает все статьи к данной категории.
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'rubric_id');
    }

    /**
     * Возращает список всех рубрик
     *
     * @return Rubric[] | Collection
     */
    public function scopeArticlePublished($query)
    {
        return $query->addSelect('id', 'title')
        ->whereHas('articles', function (Builder $builder) {
            $builder = Article::published($builder);
        });
    }
}
