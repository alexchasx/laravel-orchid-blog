<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\AsSource;

/**
 * App\Models\Rubric
 *
 * @property int $id
 * @property int $parent_id
 * @property string|null $slug
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Article[] $articles
 * @property-read int|null $articles_count
 * @method static Builder|Rubric articlePublished()
 * @method static \Database\Factories\RubricFactory factory(...$parameters)
 * @method static Builder|Rubric newModelQuery()
 * @method static Builder|Rubric newQuery()
 * @method static \Illuminate\Database\Query\Builder|Rubric onlyTrashed()
 * @method static Builder|Rubric query()
 * @method static Builder|Rubric whereDeletedAt($value)
 * @method static Builder|Rubric whereId($value)
 * @method static Builder|Rubric whereParentId($value)
 * @method static Builder|Rubric whereSlug($value)
 * @method static Builder|Rubric whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|Rubric withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Rubric withoutTrashed()
 * @mixin \Eloquent
 */
class Rubric extends Model
{
    use HasFactory;
    use AsSource;
    use SoftDeletes;

    public const SIDEBAR_CACHE_KEY = 'sidebar-rubrics';

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
