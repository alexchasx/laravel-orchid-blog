<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ArticleService
{
    private const PAGINATE = 12;
    private static array $selectColumn = ['id', 'title', 'published_at'];

    public function getPublic(?string $search): LengthAwarePaginator
    {
        return Article::published(
                Article::search($search)
            )->paginate(self::PAGINATE, self::$selectColumn);
    }

    /**
     * Выборка опубликованных статей для публичного JSON API
     * с полным набором полей карточки и фильтрами поиска/рубрики/тега.
     */
    public function getPublicForApi(?string $search, int $rubricId = 0, int $tagId = 0): LengthAwarePaginator
    {
        // Внимание: в БД колонка называется "excert" (опечатка модели).
        $query = Article::published(
            Article::query()->select([
                'id', 'title', 'excert', 'image', 'slug',
                'published_at', 'is_published', 'rubric_id', 'viewed',
            ])
        );

        if ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        if ($rubricId > 0) {
            $query->where('rubric_id', $rubricId);
        }

        if ($tagId > 0) {
            $query->whereHas('tags', fn (Builder $builder) => $builder->where('tag_id', $tagId));
        }

        return $query->with('rubric')->paginate(self::PAGINATE);
    }

    public function getNotPublic(): LengthAwarePaginator
    {
        return Article::query()->orderBy('id', 'desc')
            ->where('is_published', false)
            ->paginate(self::PAGINATE, self::$selectColumn);
    }

    public function getByRubric(int $rubricId): LengthAwarePaginator
    {
        return Article::published()
            ->where('rubric_id', $rubricId)
            ->paginate(self::PAGINATE, self::$selectColumn);
    }

    public function getByTag(int $tagId): LengthAwarePaginator
    {
        return Article::published()
            ->whereHas('tags', function (Builder $builder) use ($tagId) {
                $builder->where('tag_id', $tagId);
            })
            ->paginate(self::PAGINATE, self::$selectColumn);
    }

    public function checkAccess(Article $article): void
    {
        /** @var User $user */
        if (!$article->is_published
            && ( $user = Auth::user() )
            && !$user->isAdmin()
        ) {
            abort(403);
        }
    }
}
