<?php

namespace App\Models\Blog;

use App\Models\User;
use App\Models\Blog\Rubric;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Article extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;
    use SoftDeletes;

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
        'viewed',
        'keywords',
        'meta_desc',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime:d-m-Y',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
        'delete_at',
    ];

    protected $allowedSorts = [
        'is_published', 'id', 'rubric_id'
    ];

    protected $allowedFilters = [
        'rubric_id',
    ];

    // public function getTitleAttribute($value)
    // {
    //     return Str::title($value); // первые буквы слов - заглавные
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
                'excert',
                'content_raw',
                'published_at',
                'is_published',
            );
        }
        return $builder->whereDate('published_at', '<=', Carbon::now())
            ->where('is_published', true)
            ->orderBy('published_at', 'desc');
    }

    public static function byRubric($rubricId)
    {
        return self::published()->where('rubric_id', $rubricId);
    }

    public static function byTag(Builder $articles, $tagId)
    {
        return $articles->whereHas('tags', function (Builder $builder) use ($tagId) {
            $builder->where('tag_id', $tagId);
        });
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
        // return $this->morphMany(Comment::class, 'target');
    }

    // /**
    //  * Возращает все файлы к статье.
    //  *
    //  * @return MorphMany
    //  */
    // public function files()
    // {
    //     return $this->morphMany(File::class, 'target');
    // }

    // public static function populars(Builder $builder)
    // {
    //     return $builder->orderBy('viewed', 'desc')
    //         ->limit(3)
    //         ->get();
    // }

    // /**
    //  * Возращает все комментарии пользователя.
    //  *
    //  * @return HasMany
    //  */
    // public function articleTags()
    // {
    //     return $this->hasMany(ArticleTag::class, 'article_id');
    // }
}
