<?php

namespace App\Models;

use App\Models\User;
use App\Models\Rubric;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use League\CommonMark\CommonMarkConverter;

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

    /**
     * Автоматическое преобразование markdown-разметки поля "Контекст"
     * (content_raw) в HTML-код поля "Контент HTML" (content_html)
     * при создании и обновлении статьи.
     */
    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            if (!empty($article->content_raw)) {
                // Санитизация против Stored XSS:
                //  - 'html_input' => 'strip' вырезает сырой HTML, вставленный в Markdown;
                //  - 'allow_unsafe_links' => false запрещает опасные схемы URL (javascript: и т.п.).
                //    (в league/commonmark 2.10 по умолчанию true — ключ пишется во множественном числе).
                $config = [
                    'html_input'        => 'strip',
                    'allow_unsafe_links' => false,
                ];
                $article->content_html = (new CommonMarkConverter($config))
                    ->convert((string) $article->content_raw)
                    ->getContent();
            }

            // Автогенерация slug из заголовка, если slug не заполнен.
            if (empty($article->slug)) {
                $base = Str::slug($article->title);
                $slug = $base;
                $counter = 1;

                while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                    $slug = $base . '-' . $counter++;
                }

                $article->slug = $slug;
            }
        });
    }

    public $fillable = [
        'user_id',
        'rubric_id',
        'image',
        'slug',
        'title',
        'excert',
        // 'content_html' — производное поле, генерируется из content_raw в booted(),
        // массово не назначается (защита от прямой инъекции HTML из запроса).
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
        'published_at' => 'datetime',
    ];

    protected $allowedSorts = [
        'published_at', 'id', 'rubric_id'
    ];

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
        return $this->belongsToMany(Tag::class, 'article_tags');
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

    public static function published(?Builder $builder = null)
    {
        if (!$builder) {
            $builder = self::select(
                'id',
                'title',
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
