<?php

namespace App\Models;

use App\Models\User;
use App\Models\Rubric;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Illuminate\Support\Str;

/**
 * App\Models\Article
 *
 * @property int $id
 * @property int $rubric_id
 * @property int $user_id
 * @property string|null $slug
 * @property string $title
 * @property string|null $excert
 * @property string $content_raw
 * @property string|null $content_html
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property string|null $image
 * @property int|null $viewed Кол-во просмотров
 * @property string|null $keywords
 * @property string|null $meta_desc
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Rubric $rubric
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read User $user
 * @method static Builder|Article defaultSort(string $column, string $direction = 'asc')
 * @method static \Database\Factories\ArticleFactory factory(...$parameters)
 * @method static Builder|Article filters(?\Orchid\Filters\HttpFilter $httpFilter = null)
 * @method static Builder|Article filtersApply(iterable $filters = [])
 * @method static Builder|Article filtersApplySelection($selection)
 * @method static Builder|Article newModelQuery()
 * @method static Builder|Article newQuery()
 * @method static \Illuminate\Database\Query\Builder|Article onlyTrashed()
 * @method static Builder|Article query()
 * @method static Builder|Article search(?string $query)
 * @method static Builder|Article whereContentHtml($value)
 * @method static Builder|Article whereContentRaw($value)
 * @method static Builder|Article whereCreatedAt($value)
 * @method static Builder|Article whereDeletedAt($value)
 * @method static Builder|Article whereExcert($value)
 * @method static Builder|Article whereId($value)
 * @method static Builder|Article whereImage($value)
 * @method static Builder|Article whereIsPublished($value)
 * @method static Builder|Article whereKeywords($value)
 * @method static Builder|Article whereMetaDesc($value)
 * @method static Builder|Article wherePublishedAt($value)
 * @method static Builder|Article whereRubricId($value)
 * @method static Builder|Article whereSlug($value)
 * @method static Builder|Article whereTitle($value)
 * @method static Builder|Article whereUpdatedAt($value)
 * @method static Builder|Article whereUserId($value)
 * @method static Builder|Article whereViewed($value)
 * @method static \Illuminate\Database\Query\Builder|Article withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Article withoutTrashed()
 * @mixin \Eloquent
 */
class Article extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;
    use SoftDeletes;

    public const LENGTH_DATE = 10;

    public $fillable = [
        'user_id',
        'rubric_id',
        'image',
        'slug',
        'title',
        'excert',
        'content_html',
        'content_raw',
        'is_published',
        'published_at',
        'updated_at',
        'viewed',
        'keywords',
        'meta_desc',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        // 'published_at' => 'datetime:d-m-Y',  // не работает?
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
        'delete_at',
    ];

    protected $allowedSorts = [
        'published_at', 'id', 'rubric_id'
    ];

    protected $allowedFilters = [
        'rubric_id',
    ];

    // public function getTitleAttribute($value)
    // {
    //     return Str::title($value); // первые буквы слов - заглавные
    // }

    // public function getPublishedAtAttribute($value)
    // {
    //     return (new Carbon($value))->format('d-m-Y');
    //     // ->diffForHumans(); // Вызывает ошибку в админке Orchid (не понимает русский формат "diffForHumans")
    // }

    // public function setPublishedAtAttribute($value)
    // {
    //     $this->attributes['published_at'] = Carbon::createFromFormat('m/d/Y', $value)->format('Y-m-d');
    // }

    /**
     * Возращает категорию данной статьи.
     *
     * @return BelongsTo
     */
    public function rubric()
    {
        return $this->belongsTo(Rubric::class);
    }

    /**
     * Возращает владельца данной статьи
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Возращает тэги статьи.
     *
     * @return BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'article_tags'/*,
            'article_id',
            'tag_id'*/
        );
    }

    /**
     * Scope a query to search posts
     */
    public function scopeSearch(Builder $builder, ?string $query)
    {
        if ($query) {
            return $builder->where('title', 'LIKE', "%{$query}%");
        }
    }

    // С этим методом не работает фильтрация по тегам
    // public function scopePublished($query)
    // {
    //     return $query->addSelect(
    //         'id',
    //         'title',
    //         'excert',
    //         'content_raw',
    //         'published_at',
    //         'is_published',
    //     )->whereDate('published_at', '<=', Carbon::now())
    //         ->where('is_published', true)
    //         ->orderBy('published_at', 'desc');
    // }

    public static function published(?Builder $builder = null)
    {
        if (!$builder) {
            $builder = self::select(
                'id',
                'title',
                // 'excert',
                'content_raw',
                'published_at',
                'is_published',
            );
        }
        return $builder->whereDate('published_at', '<=', Carbon::now())
            ->where('is_published', true)
            ->with('tags')
            ->orderBy('published_at', 'desc');
    }

    public static function recents(Builder $builder)
    {
        return $builder->orderBy('published_at', 'desc')->limit(4)->get();
    }

    /**
     * Возращает все комментарии статьи.
     *
     * @return hasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }
}
