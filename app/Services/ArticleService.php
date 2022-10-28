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