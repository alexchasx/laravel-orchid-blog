<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class BlogPost extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;
    use SoftDeletes;

    public $fillable = [
        'user_id',
        'category_id',
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
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
    ];

    protected $allowedSorts = [
        'is_published', 'id', 'category_id'
    ];

    protected $allowedFilters = [
        'category_id',
    ];

    /**
     * Возращает категорию данной статьи.
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(BlogCategory::class);
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
     * Возращает все комментарии статьи.
     *
     * @return hasMany
     */
    // public function comments()
    // {
    //     return $this->hasMany(Comment::class)->orderBy('created_at');
    //     // return $this->morphMany(Comment::class, 'target');
    // }

    /**
     * Возращает все файлы к статье.
     *
     * @return MorphMany
     */
    // public function files()
    // {
    //     return $this->morphMany(File::class, 'target');
    // }

    /**
     * Возращает тэги статьи.
     *
     * @return BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'post_tags'/*,
            'post_id',
            'tag_id'*/
        );
    }

    /**
     * Возращает все комментарии пользователя.
     *
     * @return HasMany
     */
    // public function postTags()
    // {
    //     return $this->hasMany(PostTag::class, 'post_id');
    // }

    public static function allPublished()
    {
        return self::latest()
            ->where('published_at', '<=', Carbon::now())
            ->where('is_published', true);
    }

    public static function byCategory(Builder $builder, $categoryId)
    {
        return $builder->where('category_id', $categoryId);
    }

    public static function byTag(Builder $posts, $tagId)
    {
        return $posts->whereHas('tags', function (Builder $builder) use ($tagId) {
            $builder->where('tag_id', $tagId);
        });
    }

    public static function recents(Builder $builder)
    {
        return $builder->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    }

    // public static function populars(Builder $builder)
    // {
    //     return $builder->orderBy('viewed', 'desc')
    //         ->limit(3)
    //         ->get();
    // }
}
