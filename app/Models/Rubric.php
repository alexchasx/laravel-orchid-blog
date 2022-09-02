<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'slug',
        'title',
        'description',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'rubric_id');
    }

    public function scopeArticlePublished(Builder $query): Builder
    {
        return $query->addSelect(['id', 'title'])
            ->whereHas('articles', function (Builder $builder) {
                $builder = Article::published($builder);
        });
    }
}
