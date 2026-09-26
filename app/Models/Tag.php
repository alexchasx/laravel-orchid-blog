<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;

/**
 * App\Models\Tag
 *
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property int|null $popular
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $count_articles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ArticleTag[] $article_tags
 * @property-read int|null $article_tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Article[] $articles
 * @property-read int|null $articles_count
 * @method static Builder|Tag factory(...$parameters)
 * @method static Builder|Tag newModelQuery()
 * @method static Builder|Tag newQuery()
 * @method static \Illuminate\Database\Query\Builder|Tag onlyTrashed()
 * @method static Builder|Tag query()
 * @method static Builder|Tag whereActive($value)
 * @method static Builder|Tag whereCountArticles($value)
 * @method static Builder|Tag whereDeletedAt($value)
 * @method static Builder|Tag whereId($value)
 * @method static Builder|Tag wherePopular($value)
 * @method static Builder|Tag whereSlug($value)
 * @method static Builder|Tag whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|Tag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Tag withoutTrashed()
 * @mixin \Eloquent
 */
class Tag extends Model
{
    use HasFactory;
    use AsSource;
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'slug',
        'popular',
        'active',
        'count_articles',
    ];

    protected static function booted(): void
    {
        static::saving(function (Tag $tag): void {
            if (empty($tag->slug)) {
                $base = Str::slug($tag->title);
                $slug = $base;
                $counter = 1;

                while (Tag::where('slug', $slug)->where('id', '!=', $tag->id)->exists()) {
                    $slug = $base . '-' . $counter++;
                }

                $tag->slug = $slug;
            }
        });
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tags');
    }

    public function article_tags(): HasMany
    {
        return $this->hasMany(ArticleTag::class, 'tag_id');
    }

    public static function updateCountArticles(Article $article): void
    {
        $tags = $article->tags()->get();
        foreach ($tags as $tag) {
            $tag->count_articles = $tag->articles()
                ->where('is_published', true)
                ->count();
            $tag->save();
        }
    }
}
