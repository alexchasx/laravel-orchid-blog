<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    public $fillable = [
        'title',
        'description',
        'content',
        'user_id',
        'category_id',
        'image',
        'viewed',
        'keywords',
        'meta_desc',
        'published',
        'published_at',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
    ];

    /**
     * Возращает категорию данной статьи.
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
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
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
        // return $this->morphMany(Comment::class, 'target');
    }

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
            'article_tags'/*,
            'article_id',
            'tag_id'*/
        );
    }

    /**
     * Возращает все комментарии пользователя.
     *
     * @return HasMany
     */
    public function articleTags()
    {
        return $this->hasMany(ArticleTag::class, 'article_id');
    }
}
